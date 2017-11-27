<?php

/**
 * MensagemControllerTest
 *
 */
class MensagemControllerTest extends MinC_Test_ControllerActionTestCase
{

    public function testListarAction() {
        $this->autenticar();

        //reset para garantir respostas.
        $this->resetRequest()
            ->resetResponse();

        // trocar para perfil Proponente
        $this->request->setMethod('GET');
        $this->dispatch('/autenticacao/perfil/alterarperfil?codGrupo=1111&codOrgao=2222');
        $this->assertRedirectTo('/principalproponente');

        //reset para garantir respostas.
        $this->resetRequest()
            ->resetResponse();

        $this->dispatch("/solicitacao/mensagem/index");
        $this->assertModule('solicitacao');
        $this->assertController('mensagem');
        $this->assertAction('index');

        //verifica se tela carregou corretamente
        $this->assertQuery('div.container-fluid div');
    }
}
