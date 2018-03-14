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
    
}