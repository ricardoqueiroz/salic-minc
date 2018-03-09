<?php
/**
 * Default_Projetos
 *
 * @package
 */
class ProjetosModelTest extends MinC_Test_ModelTestCase
{
    public function setUp()
    {
        parent::setUp();

        // Marcado para refatoração futura
        // * fixture do banco de dados com dados controlados
        $this->idPronac = '206025';
    }

    public function testPeriodoExecucao()
    {
        $projetos = new Projetos();
        
        $periodoExecucao = $projetos->buscarPeriodoExecucao($this->idPronac);
        
        $this->assertNotEmpty($periodoExecucao->qtdDias);
        $this->assertNotEmpty($periodoExecucao->DtInicioExecucao);
        $this->assertNotEmpty($periodoExecucao->DtFimExecucao);
    }
}