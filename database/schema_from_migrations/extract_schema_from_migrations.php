<?php

declare(strict_types=1);
// Improved migration parser: applies migrations in timestamp order and
// handles Schema::create and Schema::table (add/drop/rename/indexes/foreigns)
// Produces database/schema_from_migrations.sql and .txt (more complete)

function findMigrationFiles(string $root): array
{
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    foreach ($it as $file) {
        if ($file->isFile() && preg_match('/\.php$/', (string) $file->getFilename())) {
            $path = $file->getPathname();
            if (preg_match('#(/|\\\\)database(/|\\\\)migrations(/|\\\\)#', (string) $path)) {
                $files[] = $path;
            }
        }
    }

    // sort by filename (timestamps at start) to process in chronological order
    sort($files, SORT_STRING);

    return $files;
}

function extractCreatesAndAlters(string $content): array
{
    $result = ['creates' => [], 'alters' => []];

    // Schema::create
    $patternCreate = '/Schema::create\(\s*["\']([^"\']+)["\']\s*,\s*function[^\{]*\{(.*?)\n\s*\}\s*\)\s*;/ms';
    if (preg_match_all($patternCreate, $content, $matchesC, PREG_SET_ORDER)) {
        foreach ($matchesC as $m) {
            $result['creates'][$m[1]] = $m[2];
        }
    }

    // Schema::table
    $patternTable = '/Schema::table\(\s*["\']([^"\']+)["\']\s*,\s*function[^\{]*\{(.*?)\n\s*\}\s*\)\s*;/ms';
    if (preg_match_all($patternTable, $content, $matchesT, PREG_SET_ORDER)) {
        foreach ($matchesT as $m) {
            $result['alters'][] = ['table' => $m[1], 'body' => $m[2]];
        }
    }

    return $result;
}

function parseColumnDefinitionLine(string $line): ?array
{
    // returns column attr array or null for non-column
    $line = mb_trim($line);
    if ($line === '') {
        return null;
    }

    $line = preg_replace('/\s+/', ' ', $line);
    if (!preg_match('/\$table->(\w+)\s*\((.*)\)(.*)$/', (string) $line, $m)) {
        return null;
    }

    $method = $m[1];
    $args = mb_trim($m[2]);
    $chain = $m[3];

    $colName = null;
    $colLength = null;
    // string arg
    if (preg_match('/^[\s]*["\']([^"\']+)["\'](?:\s*,\s*(\d+))?/', $args, $am)) {
        $colName = $am[1];
        if (isset($am[2])) {
            $colLength = (int) $am[2];
        }
    }

    $attr = [
        'method' => $method,
        'name' => $colName,
        'length' => $colLength,
        'nullable' => (bool) preg_match('/->nullable\(/', $chain),
        'unique' => (bool) preg_match('/->unique\(/', $chain),
        'index' => (bool) preg_match('/->index\(/', $chain),
        'default' => null,
        'unsigned' => (bool) preg_match('/->unsigned\(/', $chain),
    ];
    if (preg_match('/->default\(([^)]+)\)/', $chain, $dm)) {
        $attr['default'] = mb_trim($dm[1]);
    }

    return $attr;
}

function applyCreate(array &$schema, string $table, string $body): void
{
    if (!isset($schema[$table])) {
        $schema[$table] = ['columns' => [], 'indexes' => [], 'uniques' => [], 'primary' => null, 'foreigns' => []];
    }

    $lines = preg_split('/;\s*/', $body);
    foreach ($lines as $raw) {
        $parsed = parseColumnDefinitionLine($raw);
        if ($parsed === null) {
            continue;
        }

        // special handlers
        $m = $parsed['method'];
        if ($m === 'id') {
            $schema[$table]['primary'] = ['id'];
            $schema[$table]['columns']['id'] = ['method' => 'id'];

            continue;
        }

        if (in_array($m, ['timestamps', 'timestampsTz', 'nullableTimestamps'])) {
            $schema[$table]['columns']['created_at'] = ['method' => 'timestamp', 'name' => 'created_at', 'nullable' => true];
            $schema[$table]['columns']['updated_at'] = ['method' => 'timestamp', 'name' => 'updated_at', 'nullable' => true];

            continue;
        }

        if ($m === 'softDeletes') {
            $schema[$table]['columns']['deleted_at'] = ['method' => 'timestamp', 'name' => 'deleted_at', 'nullable' => true];

            continue;
        }

        // generic column
        $name = $parsed['name'] ?? null;
        if ($name) {
            $schema[$table]['columns'][$name] = $parsed;
        }

        // detect constrained foreign inline like ->constrained('other')
        if (preg_match('/->constrained\(\s*["\']([^"\']+)["\']/', $raw, $cm) && $name) {
            $schema[$table]['foreigns'][] = ['column' => $name, 'references' => 'id', 'on' => $cm[1]];
        }
    }
}

function applyAlter(array &$schema, string $table, string $body): void
{
    if (!isset($schema[$table])) {
        // table may be altered before create in our scan order; create placeholder
        $schema[$table] = ['columns' => [], 'indexes' => [], 'uniques' => [], 'primary' => null, 'foreigns' => []];
    }

    $lines = preg_split('/;\s*/', $body);
    foreach ($lines as $raw) {
        $line = mb_trim($raw);
        if ($line === '') {
            continue;
        }

        // dropColumn
        if (preg_match('/\$table->dropColumn\(\s*["\']([^"\']+)["\']\s*\)/', $line, $m)) {
            unset($schema[$table]['columns'][$m[1]]);

            continue;
        }

        // renameColumn
        if (preg_match('/\$table->renameColumn\(\s*["\']([^"\']+)["\']\s*,\s*["\']([^"\']+)["\']\s*\)/', $line, $m)) {
            $from = $m[1];
            $to = $m[2];
            if (isset($schema[$table]['columns'][$from])) {
                $schema[$table]['columns'][$to] = $schema[$table]['columns'][$from];
                $schema[$table]['columns'][$to]['name'] = $to;
                unset($schema[$table]['columns'][$from]);
            }

            continue;
        }

        // foreign(...) -> references(...)->on(...)
        if (preg_match('/\$table->foreign\(\s*["\']([^"\']+)["\']\s*\)(.*)/', $line, $m)) {
            $col = $m[1];
            $rest = $m[2];
            $ref = 'id';
            $on = null;
            if (preg_match('/->references\(\s*["\']([^"\']+)["\']\s*\)/', $rest, $rm)) {
                $ref = $rm[1];
            }

            if (preg_match('/->on\(\s*["\']([^"\']+)["\']\s*\)/', $rest, $om)) {
                $on = $om[1];
            }

            $schema[$table]['foreigns'][] = ['column' => $col, 'references' => $ref, 'on' => $on];

            continue;
        }

        // unique(index) or unique(['a','b'])
        if (preg_match('/\$table->unique\(\s*(\[.*?\]|["\'][^"\']+["\'])\s*\)/', $line, $m)) {
            $cols = parseArgColumns($m[1]);
            $schema[$table]['uniques'][] = $cols;

            continue;
        }

        // index
        if (preg_match('/\$table->index\(\s*(\[.*?\]|["\'][^"\']+["\'])\s*\)/', $line, $m)) {
            $cols = parseArgColumns($m[1]);
            $schema[$table]['indexes'][] = $cols;

            continue;
        }

        // primary
        if (preg_match('/\$table->primary\(\s*(\[.*?\]|["\'][^"\']+["\'])\s*\)/', $line, $m)) {
            $cols = parseArgColumns($m[1]);
            $schema[$table]['primary'] = $cols;

            continue;
        }

        // add column (use same parser as create)
        $parsed = parseColumnDefinitionLine($line);
        if ($parsed && ($parsed['name'] ?? null)) {
            $name = $parsed['name'];
            $schema[$table]['columns'][$name] = $parsed;
            // check constrained
            if (preg_match('/->constrained\(\s*["\']([^"\']+)["\']/', $line, $cm)) {
                $schema[$table]['foreigns'][] = ['column' => $name, 'references' => 'id', 'on' => $cm[1]];
            }
        }
    }
}

function parseArgColumns(string $arg): array
{
    $arg = mb_trim($arg);
    // array like ['a','b'] or single 'a'
    if (preg_match('/^\[(.*)\]$/s', $arg, $m)) {
        $inner = $m[1];
        preg_match_all('/["\']([^"\']+)["\']/', $inner, $mm);

        return $mm[1];
    }

    if (preg_match('/^["\']([^"\']+)["\']$/', $arg, $m)) {
        return [$m[1]];
    }

    return [];
}

function mapColumnToSQL(array $col): array
{
    $method = $col['method'];
    $name = $col['name'] ?? null;
    $len = $col['length'] ?? null;
    $nullable = $col['nullable'] ?? false;
    $unsigned = $col['unsigned'] ?? false;
    $default = $col['default'] ?? null;

    $sqlType = 'TEXT';
    switch ($method) {
        case 'id': return [$name ?? 'id' => 'BIGSERIAL PRIMARY KEY'];
        case 'increments': $sqlType = 'INT AUTO_INCREMENT';
            break;
        case 'bigIncrements': $sqlType = 'BIGINT AUTO_INCREMENT';
            break;
        case 'bigInteger':
        case 'unsignedBigInteger':
        case 'foreignId': $sqlType = 'BIGINT';
            break;
        case 'integer':
        case 'unsignedInteger': $sqlType = 'INT';
            break;
        case 'unsignedSmallInteger':
        case 'smallInteger': $sqlType = 'SMALLINT';
            break;
        case 'unsignedTinyInteger': $sqlType = 'TINYINT';
            break;
        case 'string': $sqlType = 'VARCHAR('.($len ?: 255).')';
            break;
        case 'text': $sqlType = 'TEXT';
            break;
        case 'longText': $sqlType = 'LONGTEXT';
            break;
        case 'boolean': $sqlType = 'BOOLEAN';
            break;
        case 'timestamp':
        case 'timestamps':
        case 'timestampsTz':
        case 'dateTime':
        case 'dateTimeTz': $sqlType = 'TIMESTAMP';
            break;
        case 'timestampTz': $sqlType = 'TIMESTAMPTZ';
            break;
        case 'date': $sqlType = 'DATE';
            break;
        case 'time': $sqlType = 'TIME';
            break;
        case 'json':
        case 'jsonb': $sqlType = mb_strtoupper((string) $method);
            break;
        case 'uuid':
        case 'foreignUuid': $sqlType = 'UUID';
            break;
        case 'decimal': $sqlType = 'DECIMAL';
            break;
        default: $sqlType = mb_strtoupper((string) $method);
    }

    $parts = [$sqlType];
    if ($unsigned && mb_stripos($sqlType, 'INT') !== false) {
        $parts[] = 'UNSIGNED';
    }

    $parts[] = $nullable ? 'NULL' : 'NOT NULL';
    if ($default !== null) {
        $parts[] = 'DEFAULT '.$default;
    }

    return [$name => mb_trim(implode(' ', $parts))];
}

function generateSqlForTables(array $tables): string
{
    $out = "-- Generated schema from migrations (applied in order)\n\n";
    foreach ($tables as $table => $info) {
        $out .= "CREATE TABLE IF NOT EXISTS {$table} (\n";
        $colsSql = [];
        foreach ($info['columns'] as $col) {
            $mapped = mapColumnToSQL($col);
            foreach ($mapped as $n => $def) {
                $colsSql[] = sprintf('    %s %s', $n, $def);
            }
        }

        // primary
        if (filled($info['primary'])) {
            $cols = implode(', ', $info['primary']);
            $colsSql[] = sprintf('    PRIMARY KEY (%s)', $cols);
        }

        $out .= implode(",\n", $colsSql)."\n);\n\n";

        // indexes
        foreach ($info['indexes'] as $idx) {
            $cols = implode(', ', $idx);
            $out .= sprintf('CREATE INDEX idx_%s_', $table).md5($cols)." ON {$table} ({$cols});\n";
        }

        // uniques
        foreach ($info['uniques'] as $u) {
            $cols = implode(', ', $u);
            $out .= sprintf('ALTER TABLE %s ADD CONSTRAINT uniq_%s_', $table, $table).md5($cols)." UNIQUE ({$cols});\n";
        }

        // foreigns
        foreach ($info['foreigns'] as $f) {
            if ($f['on']) {
                $out .= "ALTER TABLE {$table} ADD CONSTRAINT fk_{$table}_{$f['column']} FOREIGN KEY ({$f['column']}) REFERENCES {$f['on']}({$f['references']});\n";
            }
        }

        $out .= "\n";
    }

    return $out;
}

function generateTxtForTables(array $tables): string
{
    $out = "Schema extracted from migrations (applied in order)\n\n";
    foreach ($tables as $table => $info) {
        $out .= sprintf('Table: %s%s', $table, PHP_EOL);
        foreach ($info['columns'] as $name => $col) {
            $out .= sprintf('  - %s: %s', $name, $col['method']);
            if (filled($col['length'])) {
                $out .= sprintf('(%s)', $col['length']);
            }

            if (filled($col['nullable'])) {
                $out .= ' nullable';
            }

            if (filled($col['unsigned'])) {
                $out .= ' unsigned';
            }

            if (filled($col['default'])) {
                $out .= ' default='.$col['default'];
            }

            $out .= "\n";
        }

        if (filled($info['primary'])) {
            $out .= '  Primary: '.implode(', ', $info['primary'])."\n";
        }

        if (filled($info['indexes'])) {
            foreach ($info['indexes'] as $i) {
                $out .= '  Index: '.implode(', ', $i)."\n";
            }
        }

        if (filled($info['uniques'])) {
            foreach ($info['uniques'] as $u) {
                $out .= '  Unique: '.implode(', ', $u)."\n";
            }
        }

        if (filled($info['foreigns'])) {
            foreach ($info['foreigns'] as $f) {
                $out .= sprintf('  FK: %s -> %s.%s%s', $f['column'], $f['on'], $f['references'], PHP_EOL);
            }
        }

        $out .= "\n";
    }

    return $out;
}

$root = __DIR__.'/..';
$files = findMigrationFiles($root);

$schema = [];
foreach ($files as $f) {
    $content = file_get_contents($f);
    $ops = extractCreatesAndAlters($content);
    // apply creates first in this file
    foreach ($ops['creates'] as $table => $body) {
        applyCreate($schema, $table, $body);
    }

    // then applies alters (Schema::table)
    foreach ($ops['alters'] as $alter) {
        applyAlter($schema, $alter['table'], $alter['body']);
    }
}

$sql = generateSqlForTables($schema);
$txt = generateTxtForTables($schema);

$sqlPath = realpath($root).'/database/schema_from_migrations.sql';
$txtPath = realpath($root).'/database/schema_from_migrations.txt';
file_put_contents($sqlPath, $sql);
file_put_contents($txtPath, $txt);

echo sprintf('Wrote: %s%s', $sqlPath, PHP_EOL);
echo sprintf('Wrote: %s%s', $txtPath, PHP_EOL);
