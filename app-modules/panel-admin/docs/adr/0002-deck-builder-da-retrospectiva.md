---
type: adr
title: 'Deck Builder da retrospectiva: 3 colunas, preview pelo render path real'
module: panel-admin
status: accepted
date: 2026-08-04
author: Clintonrocha98
related:
    spec: community/2026-07-19-retrospectiva-multi-fonte
    adr: community/0001-retrospectiva-multi-fonte-via-contrato-de-source
---

# ADR-0002: Deck Builder da retrospectiva

**Status:** Accepted
**Date:** 2026-08-04
**Deciders:** Clintonrocha98

## Contexto

A Fase 2 entregou um CRUD Filament completo **em capacidade**: dá para ordenar fontes, ligar e desligar
blocos, escrever os textos, listar exclusions e publicar. O que ele não dá é **noção do resultado**. O
operador edita um repeater de linhas e só descobre o que fez abrindo o preview em outra aba, sem relação
visual entre o campo que mexeu e o slide que mudou.

A Fase 3 é o upgrade de UX desse mesmo poder: montar o deck vendo o deck. Por definição (ADR-0002 do
`community`) ela não inventa capacidade nova; se nunca vier, a feature continua funcionando.

Ao implementar a curadoria apareceu um buraco herdado: `SourceFilters::excludes()` existia desde a Fase 1
e o `deck_config` já gravava os refs, mas **nenhuma fonte chamava o método**. Exclusion era campo morto.
Um picker em cima disso seria UI para um botão que não faz nada.

## Decisão

### O builder substitui a página de edição

A `EditRetrospective` (formulário Filament padrão) sai; entra uma `Page` de resource registrada na chave
`edit` com rota `/{record}/deck`. Manter a chave preserva o clique na tabela e o `getUrl('edit')`; trocar
a rota deixa a URL honesta. `List` e `Create` continuam padrão: criar uma edição é preencher título e
período, não montar deck.

Duas telas editando o mesmo `deck_config` seria duas fontes de verdade de curadoria, com risco de uma
sobrescrever a outra.

### Três colunas: estrutura, preview, inspector

```
┌──────────────────┬───────────────────────────┬───────────────────────┐
│ [Estrutura]      │ [Preview]                 │ [Inspector]           │
│ capa             │  iframe da rota de        │  formulário do que    │
│ blocos de fonte  │  preview da Fase 2        │  está selecionado     │
│   chips de slide │  (mesmo ComposeDeck)      │  4 modos              │
│ fecho            │                           │                       │
└──────────────────┴───────────────────────────┴───────────────────────┘
   seleciona            só leitura                 edita e salva
```

O inspector é contextual, com quatro modos, e cada um escreve onde já se escrevia na Fase 2:

| Seleção       | Edita                                                      | Persiste em                             |
| ------------- | ---------------------------------------------------------- | --------------------------------------- |
| Capa          | título, período, ocultar bots, título e introdução da capa | colunas da edição                       |
| Bloco (fonte) | exibir, exclusions daquela fonte                           | `hidden_sources`, `exclusions`, `order` |
| Slide         | exibir                                                     | `hidden_slides`                         |
| Fecho         | mensagem de fecho                                          | coluna `closing_text`                   |

Nenhuma coluna nova, nenhuma migration: o `DeckConfig` da Fase 2 já tinha `hidden_slides` persistindo
sem UI que o editasse.

### O preview é um iframe da rota pública de preview

Nada de reimplementar o deck dentro do painel. O centro aponta para
`/comunidade/retrospectiva/{id}/preview`, a mesma rota que o operador já abria em outra aba, que passa
pelo mesmo `ComposeDeck` da página pública. Preview que mente é pior que preview nenhum, e a única
garantia de que ele não mente é ser literalmente a mesma coisa.

Custo aceito: o iframe recarrega inteiro ao salvar (com `?v={updated_at}` para furar cache), em vez de
atualizar o slide alterado no lugar.

### Reordenar por botões, não por drag

Primeiro corte com subir/desce. Drag and drop exigiria uma dependência de frontend nova (o SortableJS que
o Filament usa é interno, não é API pública) para ordenar entre 2 e 5 blocos. Fica como incremento
posterior, sem mexer no formato persistido.

### Curadoria entra por interface segregada

`CuratableSource` no `community`, com `slideCatalog()` e `exclusionCandidates(Period)`, implementada por
`GithubSource` e `DiscordSource`. O `RetrospectiveSource` não muda (ISP, previsto no ADR-0001 do
`community`). O builder checa `instanceof`: fonte que não cura aparece na timeline com ordem e on/off,
sem catálogo de slides nem picker, e o deck segue montando.

`slideCatalog()` é estático, resolvido sem tocar o banco. `exclusionCandidates()` varre dado, então é
obrigação da implementação manter a consulta escopada pelo período e com `LIMIT` (30 no GitHub, 20 no
Discord), com cache curto por `(fonte, período)`.

### On/off é por kind, não por instância de slide

`github.repos` rende um slide por repositório. O toggle esconde o bloco inteiro de repositórios. Ligar e
desligar repo a repo exigiria identidade estável por instância, que o snapshot congelado não carrega, e
seria capacidade nova (a Fase 3 não inventa capacidade).

### Ref de exclusion é namespaced por prefixo

`DeckConfig` guarda exclusions por fonte, mas `allExclusions()` achata tudo numa lista só antes de virar
`SourceFilters`. Com prefixo distinto por tipo de alvo (`pr:`, `issue:`, `actor:` no GitHub; `message:`,
`member:` no Discord) cada fonte reconhece só o que emite, sem disputa de ref e sem tabela de tradução.
No GitHub o ref de item é o próprio `external_ref` da linha.

### Exclusion passa a valer de verdade

Cada fonte aplica os refs dentro do `collect()`, antes de qualquer agregação: o que é excluído some dos
slides **e dos números**. Isso não é capacidade nova da Fase 3, é a Fase 1 sendo completada (o ADR-0001
do `community` já definia exclusion como filtro que mexe no dado).

Consequência editorial que a UI precisa dizer em voz alta: mexer em exclusion exige **republicar**, porque
recompila o snapshot. Ordem e on/off não, esses re-derivam.

## Alternativas consideradas

- **Manter o Edit e adicionar o builder como página extra** — rejeitado: duas telas escrevendo o mesmo
  `deck_config`.
- **Renderizar o deck dentro do painel (sem iframe)** — rejeitado: duplica o render path e abre espaço
  para o preview divergir do publicado; ainda importaria CSS do portal para dentro do painel.
- **Editar cada slide (título, máximo de itens, ordenação interna)** — rejeitado: é capacidade nova,
  contraria o contrato da fase e obrigaria o `ComposeDeck` a conhecer semântica de cada kind.
- **Drag and drop já no primeiro corte** — adiado: dependência nova para ordenar poucos blocos.

## Consequências

- O painel passa a depender do contrato de curadoria do `community`, não das fontes concretas. Fonte nova
  ganha builder de graça ao implementar `CuratableSource`.
- Candidatos a exclusion ficam até 5 minutos velhos depois de um backfill (cache por período).
- O picker mostra o topo do recorte, não a tabela inteira. Esconder algo fora desse teto não é possível
  pela UI (o formato persistido aceita qualquer ref, então o caminho existe se um dia for preciso).
- O iframe recarregando inteiro custa uma coleta ao vivo por salvamento em rascunho. Aceitável para o
  volume de uso (uma edição por mês, um operador).
