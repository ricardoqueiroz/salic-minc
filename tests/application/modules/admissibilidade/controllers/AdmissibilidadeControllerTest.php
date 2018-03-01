<?php

class AdmissibilidadeControllerTest extends MinC_Test_ControllerActionTestCase
{
     public function setUp()
     {
        parent::setUp();

        $this->idPreProjeto = 276034;
        
        $this->autenticar();

        $this->resetRequest()
            ->resetResponse();

        $this->alterarPerfil(Autenticacao_Model_Grupos::COORDENADOR_ADMISSIBILIDADE, Orgaos::ORGAO_GEAAP_SUAPI_DIAAPI);
        
        $this->resetRequest()
            ->resetResponse();
     }
    
    
    /**
     * TestAdmissibilidadeAvaliacao
     *
     * @access public
     * @return void
     */
    public function testAdmissibilidadeAvaliacao()
    {
        $this->dispatch('/admissibilidade/admissibilidade/listar-propostas');
        $this->assertUrl('admissibilidade','admissibilidade', 'listar-propostas');
        
        $this->assertQuery('div.container-fluid div');
    }

    /**
     * TestExibirpropostacultural
     *
     * @access public
     * @return void
     */
    public function testExibirpropostacultural()
    {
        $this->dispatch('/admissibilidade/admissibilidade/exibirpropostacultural?idPreProjeto='. $this->idPreProjeto);
        $this->assertUrl('admissibilidade','admissibilidade', 'exibirpropostacultural');
        
        $this->assertQuery('div .exibir-proposta-cultural');
    }

    /**
     * TestAlterarunidadedeanaliseproposta
     *
     * @access public
     * @return void
     */
    public function testAlterarunidadedeanaliseproposta()
    {
        $this->dispatch('/admissibilidade/admissibilidade/alterarunianalisepropostaconsulta');
        $this->assertUrl('admissibilidade','admissibilidade', 'alterarunianalisepropostaconsulta');
        
        $this->assertQuery('form table.tabela');
    }

    /**
     * TestMensagemAction
     *
     * @access public
     * @return void
     */
    public function testMensagemAction()
    {
        $this->dispatch('/admissibilidade/mensagem' . '?idPronac=' . $this->idPronac);
        $this->assertUrl('admissibilidade','mensagem', 'index');
    }

    /**
     * TestDesarquivarPropostaAction
     *
     * @access public
     * @return void
     */
    public function testDesarquivarPropostaAction()
    {
        $this->dispatch('/admissibilidade/admissibilidade/desarquivarpropostas');
        $this->assertUrl('admissibilidade','admissibilidade', 'desarquivarpropostas');
    }

    /**
     * TestRedistribuiranaliseAction
     *
     * @access public
     * @return void
     */
    public function testRedistribuiranaliseAction()
    {
        $this->dispatch('/admissibilidade/admissibilidade/redistribuiranalise');
        $this->assertUrl('admissibilidade','admissibilidade', 'redistribuiranalise');
    }

    /**
     * TestGerenciaranalistasAction
     *
     * @access public
     * @return void
     */
    public function testGerenciaranalistasAction()
    {
        $this->dispatch('/admissibilidade/admissibilidade/gerenciaranalistas');
        $this->assertUrl('admissibilidade','admissibilidade', 'gerenciaranalistas');
    }

    /**
     * TestGerenciaranalistasLista
     *
     * @access public
     * @return void
     */
    public function testGerenciaranalistaAction()
    {
        $this->dispatch('/admissibilidade/admissibilidade/gerenciaranalista?usu_cod=6927&usu_orgao=262&gru_codigo=92');
        $this->assertUrl('admissibilidade','admissibilidade', 'gerenciaranalista');
    }
}
