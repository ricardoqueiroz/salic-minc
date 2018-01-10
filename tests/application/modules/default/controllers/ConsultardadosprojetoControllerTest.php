<?php
class  ConsultardadosprojetoControllerTest  extends MinC_Test_ControllerActionTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->idPreProjeto = '240102';
        $this->autenticar();

        //reset para garantir respostas.
        $this->resetRequest()
            ->resetResponse();

        // trocar para perfil Proponente
        $this->perfilParaProponente();

        //reset para garantir respostas.
        $this->resetRequest()
            ->resetResponse();
    }

    /**
     *
     * @access public
     * @return void
     */
    public function testListarpropostaAction()
    {
        $this->dispatch('/proposta/manterpropostaincentivofiscal/listarproposta');
        $this->assertModule('proposta');
        $this->assertController('manterpropostaincentivofiscal');
        $this->assertAction('listarproposta');

        //verifica se tela carregou corretamente
        $this->assertQuery('div.container-fluid div');
    }
}
