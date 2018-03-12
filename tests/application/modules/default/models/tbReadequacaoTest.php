<?php
/**
 * Default_TbReadequacao
 *
 * @package
 */
class TbReadequacaoModelTest extends MinC_Test_ModelTestCase
{
    public function setUp()
    {
        parent::setUp();

        // Marcado para refatoração futura
        // * fixture do banco de dados com dados controlados
        $this->idPronac = '206025';
    }

    public function testExisteReadequacaoEmAndamento()
    {
        $tbReadequacao = new tbReadequacao();
        
        $existeReadequacaoEmAndamento = $tbReadequacao->existeReadequacaoEmAndamento($this->idPronac);
        $this->assertTrue($existeReadequacaoEmAndamento);
    }

    public function testExisteReadequacaoParcialEmAndamento()
    {
        $tbReadequacao = new tbReadequacao();
        
        $existeReadequacaoEmAndamento = $tbReadequacao->existeReadequacaoEmAndamento(
            $this->idPronac,
               tbReadequacao::TIPO_READEQUACAO_REMANEJAMENTO_PARCIAL
        );
        
        $this->assertFalse($existeReadequacaoEmAndamento);
    }

    public function testExisteReadequacaoPlanilhaEmAndamento()
    {
        $tbReadequacao = new tbReadequacao();
        
        $existeReadequacaoEmAndamento = $tbReadequacao->existeReadequacaoEmAndamento(
            $this->idPronac,
               tbReadequacao::TIPO_READEQUACAO_PLANILHA_ORCAMENTARIA
        );
        
        $this->assertTrue($existeReadequacaoEmAndamento);
    }
    
    public function testDisponivelParaEdicaoReadequacaoPlanilha()
    {
        $tbReadequacao = new tbReadequacao();
        
        $disponivelParaEdicaoReadequacaoPlanilha = $tbReadequacao->disponivelParaEdicaoReadequacaoPlanilha($this->idPronac);
        $this->assertTrue($disponivelParaEdicaoReadequacaoPlanilha);
    }

    public function testDisponivelParaAdicaoItensReadequacaoPlanilha()
    {
        $tbReadequacao = new tbReadequacao();
        
        $disponivelParaEdicaoReadequacaoPlanilha = $tbReadequacao->disponivelParaAdicaoItensReadequacaoPlanilha($this->idPronac);
        $this->assertTrue($disponivelParaEdicaoReadequacaoPlanilha);
    }
}