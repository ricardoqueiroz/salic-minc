<?php
/**
 * Default_TbPlanilhaAprovacao
 *
 * @package
 */
class TbPlanilhaAprovacaoTest extends MinC_Test_ModelTestCase
{
    public function setUp()
    {
        parent::setUp();

        // Marcado para refatoração futura
        // * fixture do banco de dados com dados controlados
        $this->idPronac = '206025';
    }

    public function testValorTotalPlanilha()
    {
        $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
        
        $where = array();
        $where['a.IdPRONAC = ?'] = $this->idPronac;
        $where['a.stAtivo = ?'] = 'S';
        
        $result = $tbPlanilhaAprovacao->valorTotalPlanilha($where);
        
        $this->assertNotEmpty($result->current()['Total']);
    }

}