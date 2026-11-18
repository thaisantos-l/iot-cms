# Etapa 05 — Blog, cases e páginas internas

Situação: em implementação em 18/11/2026, branch criada da main após merge `a9474d2`. Depende da etapa 04. Requisitos: R02, R05, R06, R11. Branch sugerida: `feat/05-blog-cases-paginas`.

## Entregáveis

- Implementar `home.php`, `single.php`, `archive-iot_case.php`, `single-iot_case.php`, páginas, arquivos, busca e 404; finalizar template legal e sumário.
- Criar cards, paginação, breadcrumbs e relacionados compartilhados; adaptar o desenho de exemplos sem importar seus registros fictícios.
- Ligar seções blog/cases à consulta pública, excluindo itens com senha, privados, rascunhos, pendentes, futuros e lixeira. Aplicar os mesmos critérios à contagem e aos links condicionais.
- Renderizar zero/um/vários itens conforme o contrato. Arquivo vazio continua acessível com mensagem útil; home e links condicionais escondem a coleção vazia.
- Definir `/blog/` como página de posts e `/cases/` como arquivo do CPT, sem página com slug conflitante. Definir permalinks dos itens, mapa de URLs legadas e política dos exemplos sem equivalente.
- Configurar rewrite apenas na ativação/migração necessária, nunca com flush em toda requisição. Evitar consultas por card e restaurar contexto de consultas secundárias.

## Aceite e verificações

T05–T10, T16 e parcela de T20: testar todo ciclo 0→1→vários→0, inclusive logado como administrador na navegação pública. Testar relacionados, filtros, paginação, busca, capa ausente e preview autenticado. URL inexistente retorna HTTP 404 real.

Publicação agendada não aparece enquanto o status for `future`; a execução pontual do agendador e os caches serão validados com infra na etapa 08. Fixtures ficam em ambiente de teste e não se tornam conteúdo de produção.

## Commits e merge

1. `feat: criar templates de blog cases e paginas`
2. `feat: aplicar consultas publicas e navegacao condicional`
3. `test: cobrir estados de publicacao e rotas`

Merge com listagens coerentes em todos os estados. Preservar dados e slugs em rollback; mudança de permalink exige mapa compatível, sem redirecionar URLs para páginas inexistentes.
