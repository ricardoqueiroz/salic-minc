## DEPRECATED

# Fluxo comum de trabalho

Além da master, existem duas branches principais de trabalho no SALIC, identificadas com as duas frentes de trabalho:

               ---- dev-novaIN ------
              /
    master   o-----------------------
              \
               ---- dev-backlog -----

Cada desenvolvedor criará uma branch a partir de uma das duas.

## Criando uma branch a partir da branch dev
    
    $ git checkout dev-backlog
    $ git checkout -b dev-backlog-fulano


    master   o-------------------------------
              \
               o------- dev-backlog ---------
                \
                 o--- dev-backlog-fulano ----

## Faça commits na sua branch e envie para o gitlab (origin)
    $ git commit -m 'fix: funcionalidade x'
    $ git commit -m 'fix: funcionalidade y'
    $ git push origin dev-backlog-fulano

    master   o-----------------------------------------
              \
               o---------------- dev-backlog ----------
                \
                 o---o----o----- dev-backlog-fulano ---

## Atualizando sua branch com as alterações mais recentes do dev

    $ git checkout dev-backlog-fulano
    $ git fetch
    $ git merge dev-backlog
    $ git push origin dev-backlog-fulano

    master   o----------------------------------------------
              \
               o-----o----o----o--- dev-backlog ------------
                \               \
                 o---o----o------o--- dev-backlog-fulano ---

## Enviando suas alterações para a branch dev

    $ git checkout dev-backlog
    $ git fetch
    $ git merge dev-backlog-fulano
    $ git push origin dev-backlog

    master   o-----------------------------------------------------------
              \
               o-----------------------o ------ dev-backlog -------------
                \                     /
                 o---o----o------o---o -------- dev-backlog-fulano ------


## Homologando e publicando uma versão para a master

Certifique-se de que está na branch correta. Faça os testes; caso encontre bugs pequenos (1), não há problema que a correção seja feita diretamente na branch dev-backlog mesmo. Caso se tratem de alterações maiores (2), melhor voltar a trabalhar no seu branch para só então publicar o resultado na dev-backlog:

(1)

    $ git checkout dev-backlog
    $ git commit -m 'fix: pequena correcao'
    $ git push origin dev-backlog

(2)

    $ git checkout dev-backlog-fulano
    $ git commit -m 'fix maior'
    $ git checkout dev-backlog
    $ git merge dev-backlog-fulano
    $ git push origin dev-backlog-fulano


Se estiver tudo ok, edite o CHANGELOG e crie a tag

    $ vim CHANGELOG
    $ git add CHANGELOG
    $ git commit -m 'adicionando alteracoes do CHANGELOG'
    $ git tag -a v1.7.1 -m 'release de correcoes na proposta'
    $ git push origin dev-backlog
    

Na produção:

    $ git fetch
    $ git checkout -b v1.7.1


