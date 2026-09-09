# Moodle Block IBAP Navigation

Plugin de bloco para Moodle que adiciona navegação sequencial moderna entre conteúdos do curso:

- **Voltar** — atividade/recurso anterior visível ao usuário.
- **Página inicial da jornada** — retorna para a página principal do curso.
- **Continuar** — próxima atividade/recurso visível ao usuário.

## Arquitetura 2.0

A versão 2.0 foi convertida de `local_ibapnav` para **`block_ibapnav`** para usar o mesmo mecanismo de ativação e saída do plugin legado Navigation buttons.

### Ativação explícita por curso

O plugin só é habilitado quando o bloco **Navegação de conteúdo IBAP** é adicionado a um curso. Ao adicionar o bloco, `instance_create()` cria ou ativa um registro na tabela `block_ibapnav` com `enabled = 1`. Ao remover o bloco, `instance_delete()` desativa a navegação daquele curso.

### Renderização forçada

A navegação é gerada por `block_ibapnav_before_footer()`, o mesmo padrão utilizado pelo plugin legado que já funciona no Moodle. Se o curso estiver habilitado e `$PAGE->cm` existir, o plugin tenta desenhar a navegação. Não há filtros por tema, DOM, URL ou `pagetype`.

Comentários HTML de diagnóstico são incluídos quando a navegação não pode ser exibida, por exemplo:

- `IBAPNAV: block not enabled for course`
- `IBAPNAV: no course module`
- `IBAPNAV: could not resolve navigation`

### Sequência anterior/próximo

A resolução percorre diretamente `get_fast_modinfo($COURSE)->cms`, como o plugin legado. Rótulos e módulos invisíveis ao usuário são ignorados. O módulo anterior visível é guardado até encontrar o atual; o primeiro módulo visível seguinte vira o botão Continuar.

## Compatibilidade

- Moodle **4.4 ou superior**.
- Não depende de Bootstrap, jQuery, YUI, AMD ou componentes reativos.
- Não depende do DOM de um tema específico.
- CSS isolado em `#ibapnav`.
- Sem JavaScript.

## Instalação

**Importante:** esta versão é um plugin do tipo `block`, não `local`.

A pasta final deve ser:

```text
blocks/ibapnav/
```

Ela deve conter diretamente `version.php`, `block_ibapnav.php`, `lib.php`, `styles.css`, `db/`, `classes/` e `lang/`.

Se uma versão antiga de `local_ibapnav` estiver instalada em `local/ibapnav`, desinstale/remova essa versão antes de instalar a 2.0.

Depois da instalação:

1. Acesse o curso.
2. Ative o modo de edição.
3. Adicione o bloco **Navegação de conteúdo IBAP**.
4. Abra uma atividade/recurso do curso.
5. Os botões devem aparecer no final da página.

## Configurações globais

Nas configurações do bloco é possível:

- mostrar/ocultar o botão central da página do curso;
- mostrar/ocultar o nome da atividade anterior/próxima.

## Privacidade

O plugin não armazena dados pessoais. A tabela própria contém apenas o ID do curso, estado de ativação e timestamps.
