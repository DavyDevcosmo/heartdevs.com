<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> */
    private const array ISO3_TO_ISO2 = [
        'BRA' => 'BR',
        'USA' => 'US',
        'PRT' => 'PT',
        'ARG' => 'AR',
        'DEU' => 'DE',
        'CAN' => 'CA',
        'GBR' => 'GB',
        'FRA' => 'FR',
        'ESP' => 'ES',
        'ITA' => 'IT',
        'JPN' => 'JP',
        'AUS' => 'AU',
        'MEX' => 'MX',
        'COL' => 'CO',
        'CHL' => 'CL',
        'URY' => 'UY',
        'IRL' => 'IE',
        'NLD' => 'NL',
    ];

    /** @var array<string, string> */
    private const array BR_STATE_CODE_TO_NAME = [
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapá',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceará',
        'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo',
        'GO' => 'Goiás',
        'MA' => 'Maranhão',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Pará',
        'PB' => 'Paraíba',
        'PR' => 'Paraná',
        'PE' => 'Pernambuco',
        'PI' => 'Piauí',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondônia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'São Paulo',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins',
    ];

    public function up(): void
    {
        Schema::table('addresses', static function (Blueprint $table): void {
            $table->string('state', 100)->nullable()->change();
        });

        foreach (self::ISO3_TO_ISO2 as $iso3 => $iso2) {
            DB::table('addresses')
                ->where('country', $iso3)
                ->update(['country' => $iso2]);
        }

        foreach (self::BR_STATE_CODE_TO_NAME as $code => $name) {
            DB::table('addresses')
                ->where('country', 'BR')
                ->where('state', $code)
                ->update(['state' => $name]);
        }
    }

    public function down(): void
    {
        foreach (self::BR_STATE_CODE_TO_NAME as $code => $name) {
            DB::table('addresses')
                ->where('country', 'BR')
                ->where('state', $name)
                ->update(['state' => $code]);
        }

        foreach (self::ISO3_TO_ISO2 as $iso3 => $iso2) {
            DB::table('addresses')
                ->where('country', $iso2)
                ->update(['country' => $iso3]);
        }

        Schema::table('addresses', static function (Blueprint $table): void {
            $table->string('state', 4)->nullable()->change();
        });
    }
};
