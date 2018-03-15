<?php
/**
 * ReadequacoesControllerTest
 *
 * @package
 * @author
 */
class ReadequacoesControllerTest extends MinC_Test_ControllerActionTestCase
{
    public function setUp()
    {
        parent::setUp();

        // Marcado para refatoração futura
        // * fixture do banco de dados com dados controlados
        $tbReadequacao = new tbReadequacao();
        $this->idPronac = $tbReadequacao->buscarIdPronacReadequacaoEmAndamento(tbReadequacao::TIPO_READEQUACAO_PLANILHA_ORCAMENTARIA);
        
        $this->hashPronac = Seguranca::encrypt($this->idPronac);
        
        $this->autenticar();
        
        $this->resetRequest()
            ->resetResponse();
        
        $this->perfilParaProponente();
        
        $this->resetRequest()
            ->resetResponse();
        
    }

    /**
     * TestIndexAction
     *
     * @access public
     * @return void
     */    
    public function testIndexAction()
    {
        $this->dispatch('/readequacoes?idPronac=' . $this->hashPronac);
        $this->assertUrl('default','readequacoes', 'index');
    }

    /**
     * TestIndexIdPassandoUfAction
     *
     * @access public
     * @return void
     */    
    public function TestIndexIdPassandoUfAction()
    {
        $iduf = 43; // RS
        
        $this->request->setMethod('POST')
            ->setPost([
                'iduf' => $iduf
            ]);        
        $this->dispatch('/readequacoes?idPronac=' . $this->hashPronac);
        $this->assertUrl('default', 'readequacoes', 'index');
    }
    
    /**
     * TestPlanilhaOrcamentariaAction
     *
     * @access public
     * @return void
     */    
    public function testPlanilhaOrcamentariaAction()
    {
        $this->dispatch('/readequacoes/planilha-orcamentaria?idPronac=' . $this->hashPronac);
        $this->assertUrl('default','readequacoes', 'planilha-orcamentaria');
    }

    /**
     * TestPlanilhaOrcamentariaCondicoesNaoSatisfeitas()
     *
     * @access public
     * @return void
     */    
    public function testPlanilhaOrcamentariaCondicoesNaoSatisfeitas()
    {
        $tbReadequacao = new tbReadequacao();
        $possuiReadequacao = $tbReadequacao->existeReadequacaoEmAndamento($this->idPronac);
        
        $this->assertTrue($possuiReadequacao);

    }

    /**
     * TestPainelAction
     * 
     * @access public
     * @return void
     */
    public function testPainelAction()
    {
        $this->alterarPerfil(Autenticacao_Model_Grupos::COORDENADOR_ACOMPANHAMENTO, Orgaos::ORGAO_GEAR_SACAV);
        
        $this->dispatch('/readequacoes/painel');
        $this->assertUrl('default','readequacoes', 'painel');
        
    }

    /**
     * TestPainelEmAnaliseAction
     * 
     * @access public
     * @return void
     */
    public function testPainelEmAnaliseAction()
    {
        $this->alterarPerfil(Autenticacao_Model_Grupos::COORDENADOR_ACOMPANHAMENTO, Orgaos::ORGAO_GEAR_SACAV);
        
        $this->dispatch('/readequacoes/painel?pronac=&qtde=10&tipoFiltro=em_analise');
        $this->assertUrl('default','readequacoes', 'painel');
        
    }

    /**
     * TestPainelEmAnalisadosAction
     * 
     * @access public
     * @return void
     */
    public function testPainelEmAnalisadosAction()
    {
        $this->alterarPerfil(Autenticacao_Model_Grupos::COORDENADOR_ACOMPANHAMENTO, Orgaos::ORGAO_GEAR_SACAV);
        
        $this->dispatch('/readequacoes/painel?pronac=&qtde=10&tipoFiltro=em_analisados');
        $this->assertUrl('default','readequacoes', 'painel');
        
    }

    /**
     * TestPainelAguardandoPublicacaoAction
     * 
     * @access public
     * @return void
     */
    public function testPainelAguardandoPublicacaoAction()
    {
        $this->alterarPerfil(Autenticacao_Model_Grupos::COORDENADOR_ACOMPANHAMENTO, Orgaos::ORGAO_GEAR_SACAV);
        
        $this->dispatch('/readequacoes/painel?pronac=&qtde=10&tipoFiltro=aguardando_publicacao');
        $this->assertUrl('default','readequacoes', 'painel');
        
    }

    /**
     * TestPainelAnalisadosPronac
     * 
     * @access public
     * @return void
     */
    public function TestPainelAnalisadosPronacAction()
    {
        $this->alterarPerfil(Autenticacao_Model_Grupos::COORDENADOR_ACOMPANHAMENTO, Orgaos::ORGAO_GEAR_SACAV);
        
        $idPronac = 154566;
        $this->dispatch('/readequacoes/painel?pronac=' . $idPronac . '&qtde=10&tipoFiltro=aguardando_publicacao');
        $this->assertUrl('default','readequacoes', 'painel');        
    }

}