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
}
