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
        
        $this->idPronac = '209649';
        $this->hashPronac = '501eac548e7d4fa987034573abc6e179MjA4OTQ3ZUA3NWVmUiEzNDUwb3RT';
        
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
        // puxar um projetos com condições não satisfeitos
        // verificar que não consegue acessar página de readequação de planilha
        
        /* Condições para realizar uma readequação de planilha orçamentária
         
         Regras
         - NÃO POSSUIR ((readequação OU remanejamento 50%) E (em andamento)))
         - POSSUIR (
         (contrato de patrocínio) OU
         ((plano de execução imediata) E
         (anual OU bienal OU trienal OU quadrienal)
         ))
         - POSSUIR (plano de execução vigente))
         
         Ação
         - ACESSAR url

        */
    }
    
}