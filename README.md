# Moodle Block IBAP Navigation

Plugin de bloco para Moodle que adiciona uma navegação em formato de pílula no final dos conteúdos do curso:

- **Voltar** — atividade/recurso anterior visível ao usuário.
- **Página inicial da jornada** — retorna para a página principal do curso.
- **Continuar** — próxima atividade/recurso visível ao usuário.
- **Concluir jornada** — aparece quando não existe próximo conteúdo.

## Versão atual

**2.3.0** — arquitetura revisada para Moodle 4.4+.

A renderização usa a Hook API oficial do Moodle:

`core\hook\output\before_footer_html_generation`

Não existe mais callback legado `before_footer`, e o hook não depende de funções globais em `lib.php`.

## Ativação por curso

O plugin é habilitado quando o bloco **Navegação de conteúdo IBAP** é adicionado ao curso.

- Apenas uma instância do bloco é permitida por curso.
- Ao adicionar o bloco, o curso é ativado na tabela `block_ibapnav`.
- Ao remover o bloco, a navegação é desativada para aquele curso.
- As preferências do bloco continuam configuráveis por curso.

O bloco visual de administração é mostrado apenas para usuários com permissão de gerenciar atividades. A pílula de navegação é inserida separadamente pelo hook de saída.

## Renderização contextual

O plugin não tenta substituir a navegação interna das atividades.

A pílula só é considerada em páginas canônicas do tipo:

```text
/mod/{modname}/view.php
```

Isso evita conflito com páginas internas como:

- tentativas, resumo e revisão de **Quiz**;
- player de **SCORM**;
- telas internas de **Feedback** e módulos equivalentes;
- páginas internas de **Lesson**;
- fluxos de envio/edição de **Assignment**.

### Quiz

Para estudantes, a pílula não aparece antes da primeira tentativa concluída.

Ela passa a aparecer na página principal do Quiz depois que existe ao menos uma tentativa com estado `finished`. Professores e gerentes continuam vendo a navegação na página principal para fins de edição e teste.

### H5P

O H5P executa parte da navegação dentro do próprio `view.php` via JavaScript. A versão 2.3.0 não interfere no player H5P, mas também não tenta detectar dinamicamente o último slide/tela de todos os tipos de conteúdo H5P. Esse caso deve ser testado separadamente caso uma atividade H5P específica use setas próprias.

## Ordem anterior/próximo

A sequência é calculada usando as seções reais retornadas pelo Moodle por `get_fast_modinfo()->get_sections()`.

O plugin:

- respeita a ordem das atividades no curso;
- ignora rótulos;
- ignora módulos invisíveis ao usuário;
- ignora módulos que não fornecem uma URL de visualização válida;
- não inventa URLs `/view.php` para plugins que não possuem uma.

## Banco de dados e atualização

A versão 2.3.0 inclui recuperação para instalações interrompidas:

- se a tabela `block_ibapnav` estiver ausente durante upgrade, ela é recriada a partir de `db/install.xml`;
- definições XMLDB redundantes foram removidas;
- registros duplicados antigos por curso são consolidados durante o upgrade;
- chamadas de renderização não consultam a tabela enquanto instalação/upgrade estiver em andamento.

A tabela armazena apenas configurações por curso, sem dados pessoais de alunos.

## Compatibilidade

- Moodle **4.4 ou superior**.
- PHP compatível com a versão do Moodle instalada.
- Sem dependência de Bootstrap, jQuery ou YUI.
- Sem JavaScript próprio.
- CSS isolado em `#ibapnav`.
- Compatível com Moodle instalado na raiz ou em subdiretório.

## Instalação

Este é um plugin do tipo **block**.

A pasta final deve ser:

```text
blocks/ibapnav/
```

Ela deve conter diretamente:

```text
block_ibapnav.php
version.php
styles.css
classes/
db/
lang/
```

Se ainda existir uma versão antiga em:

```text
local/ibapnav/
```

desinstale/remova essa versão antes de usar o bloco atual.

### Atualização por ZIP

1. Baixe o ZIP da branch `main`.
2. No instalador do Moodle, escolha **Tipo de plugin: Bloco (block)**.
3. Se o Moodle solicitar o nome da pasta raiz, use `ibapnav`.
4. Execute a atualização do banco.
5. Limpe os caches do Moodle.
6. Em um curso, ative o modo de edição e adicione **Navegação de conteúdo IBAP**.

## Teste recomendado após atualização

Valide ao menos estes cenários com uma conta de estudante:

1. Recurso Página/Arquivo: pílula visível no final.
2. Quiz antes de responder: pílula oculta.
3. Quiz durante tentativa: pílula oculta.
4. Quiz após finalizar e voltar à página principal: pílula visível.
5. Lesson em página interna: pílula oculta.
6. SCORM dentro do player: pílula oculta.
7. Última atividade do curso: botão **Concluir jornada**.
8. Curso sem o bloco IBAP: nenhuma pílula.

## Diagnóstico

Em **Administração do site → Plugins → Blocos → Navegação de conteúdo IBAP**, o modo de diagnóstico pode ser habilitado temporariamente.

Quando ativo, o plugin adiciona comentários HTML como:

```text
IBAPNAV: block not enabled for course
IBAPNAV: hidden on internal activity page
IBAPNAV: settings table not available
```

Mantenha o modo de diagnóstico desligado em produção.

## Privacidade

O plugin não armazena dados pessoais. A tabela própria contém apenas o ID do curso, preferências de interface, estado de ativação e timestamps.
