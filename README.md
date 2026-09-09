# Moodle Local IBAP Navigation

Plugin local para Moodle que adiciona navegação sequencial entre conteúdos do curso com três controles:

- **Voltar** — atividade/recurso anterior visível ao usuário.
- **Página inicial da jornada** — retorna para a página principal do curso.
- **Continuar** — próxima atividade/recurso visível ao usuário.

## Compatibilidade

- Moodle **4.4 ou superior**.
- Projetado para funcionar independentemente do tema visual.
- Não depende de Bootstrap, jQuery, YUI, AMD, componentes reativos ou classes CSS do tema.
- Usa os hooks oficiais de saída do Moodle para carregar o CSS e renderizar a navegação.
- A navegação é inserida após a região principal do conteúdo, e não presa ao HTML específico do rodapé de um tema.

## Como instalar

1. Baixe o repositório como ZIP.
2. Garanta que a pasta final se chame `ibapnav`.
3. Coloque a pasta em `local/ibapnav` no Moodle ou instale o ZIP pelo instalador de plugins.
4. Acesse **Administração do site > Notificações** para concluir a instalação.
5. Limpe os caches do Moodle após instalar ou atualizar.

## Configurações

Em **Administração do site > Plugins > Plugins locais > Navegação de conteúdo IBAP**:

- mostrar ou ocultar o botão central da jornada;
- mostrar ou ocultar o nome da atividade anterior/próxima.

## Regras de navegação

O plugin usa a ordem real das atividades no curso e ignora automaticamente:

- rótulos (`label`);
- atividades sem página de visualização;
- atividades sem URL;
- atividades que o usuário não pode visualizar.

A navegação aparece apenas nas páginas principais de visualização de atividades e recursos (`mod-*-view`). Ela não aparece em tentativas de quiz, edição, correção, relatórios ou telas administrativas.

## CSS e temas

Todo o CSS está isolado sob `#local-ibapnav`, reduzindo conflitos com temas personalizados. O arquivo `styles.css` também é carregado explicitamente pelo hook de cabeçalho do Moodle. Os seletores do plugin não dependem de classes Bootstrap ou de nomes internos de qualquer tema.

## JavaScript

Este plugin não usa JavaScript.
