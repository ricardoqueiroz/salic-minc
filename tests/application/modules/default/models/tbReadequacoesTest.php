<?php
/**
 * Default_TbReadequacoes
 *
 * @package
 */
class TbReadequacoesModelTest extends MinC_Test_ModelTestCase
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
            null,
            tbReadequacao::TIPO_READEQUACAO_REMANEJAMENTO_PARCIAL
        );
        
        $this->assertFalse($existeReadequacaoEmAndamento);
    }

    public function testExisteReadequacaoPlanilhaEmAndamento()
    {
        $tbReadequacao = new tbReadequacao();
        
        $existeReadequacaoEmAndamento = $tbReadequacao->existeReadequacaoEmAndamento(
            $this->idPronac,
            null,
            tbReadequacao::TIPO_READEQUACAO_PLANILHA_ORCAMENTARIA
        );
        
        $this->assertTrue($existeReadequacaoEmAndamento);
    }
    
    public function testExisteReadequacaoEmAndamentoAgenteIgual()
    {
        $idAgente = 9343;
        $tbReadequacao = new tbReadequacao();
        
        $existeReadequacaoEmAndamento = $tbReadequacao->existeReadequacaoEmAndamento($this->idPronac, $idAgente);
        $this->assertTrue($existeReadequacaoEmAndamento);
    }

    public function testExisteReadequacaoEmAndamentoAgenteDiferente()
    {
        $idAgente = 9344;
        $tbReadequacao = new tbReadequacao();
        
        $existeReadequacaoEmAndamento = $tbReadequacao->existeReadequacaoEmAndamento($this->idPronac, $idAgente);
        $this->assertFalse($existeReadequacaoEmAndamento);
    }
    
    public function testDisponivelParaEdicaoReadequacaoPlanilha()
    {
        $idAgente = 9343;
        $tbReadequacao = new tbReadequacao();
        
        $disponivelParaEdicaoReadequacaoPlanilha = $tbReadequacao->disponivelParaEdicaoReadequacaoPlanilha($this->idPronac, $idAgente);
        $this->assertTrue($disponivelParaEdicaoReadequacaoPlanilha);
    }

    public function testDisponivelParaAdicaoItensReadequacaoPlanilha()
    {
        $idAgente = 9343;
        $tbReadequacao = new tbReadequacao();
        
        $disponivelParaEdicaoReadequacaoPlanilha = $tbReadequacao->disponivelParaAdicaoItensReadequacaoPlanilha($this->idPronac, $idAgente);
        $this->assertTrue($disponivelParaEdicaoReadequacaoPlanilha);
    }
}