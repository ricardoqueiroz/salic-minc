<?php

class PrestacaoContas_VisualizarProjetoController extends  MinC_Controller_Action_Abstract
{
    public function init ()
    {
        $PermissoesGrupo = [
            Autenticacao_Model_Grupos::TECNICO_PRESTACAO_DE_CONTAS,
            Autenticacao_Model_Grupos::COORDENADOR_PRESTACAO_DE_CONTAS
        ];

        $auth = Zend_Auth::getInstance();

        isset($auth->getIdentity()->usu_codigo) ? parent::perfil(1, $PermissoesGrupo) : parent::perfil(4, $PermissoesGrupo);

        isset($auth->getIdentity()->usu_codigo) ? $this->idUsuario = $auth->getIdentity()->usu_codigo : $this->idUsuario = $auth->getIdentity()->IdUsuario;

        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');

        if (isset($auth->getIdentity()->usu_codigo)) {

            $this->codGrupo = $GrupoAtivo->codGrupo;
            $this->codOrgao = $GrupoAtivo->codOrgao;
            $this->codOrgaoSuperior = (!empty($auth->getIdentity()->usu_org_max_superior)) ? $auth->getIdentity()->usu_org_max_superior : null;
        }

        parent::init();
    }

    public function indexAction()
    {
        $this->_helper->json([]);
    }

    public function dadosProjetoAction()
    {
        $idPronac = $this->_request->getParam("idPronac");

        $itens = new PrestacaoContas_Model_vwComprovacaoFinanceiraProjetoPorItemOrcamentario();

        $consolidacaoPorProduto = $itens->consolidacaoPorProduto($idPronac);
        $itensAux = [];
        $json = [];
        foreach($consolidacaoPorProduto as $k => $item){
            $itensAux[$k]['dsProduto'] = utf8_encode($item->dsProduto);
            $itensAux[$k]['qtComprovantes'] = utf8_encode($item->qtComprovantes);
            $itensAux[$k]['vlComprovado'] = utf8_encode($item->vlComprovado);
            $itensAux[$k]['PercComprovado'] = utf8_encode($item->PercComprovado);
        }
        $json['consolidacaoPorProduto']['lines'] =  $itensAux;
        $json['consolidacaoPorProduto']['cols'] = ['Produto', 'Qtd. Comprovantes', 'Valor Comprovado', '% Comprovado'];
        $json['consolidacaoPorProduto']['title'] = 'COMPROVAÇÃO CONSOLIDADA POR PRODUTO';

        $consolidadoPorEtapa = $itens->consolidadoPorEtapa($idPronac);
        $itensAux = [];
        foreach($consolidadoPorEtapa as $k => $item){
            /* $itensAux[$k]['IdPRONAC'] = utf8_encode($item->IdPRONAC); */
            $itensAux[$k]['Descricao'] = utf8_encode($item->Descricao);
            $itensAux[$k]['qtComprovantes'] = utf8_encode($item->qtComprovantes);
            $itensAux[$k]['vlComprovado'] = utf8_encode($item->vlComprovado);
            $itensAux[$k]['PercComprovado'] = utf8_encode($item->PercComprovado);
        }
        $json['consolidadoPorEtapa']['lines'] = $itensAux;
        $json['consolidadoPorEtapa']['cols'] = ['Descricao', 'Qtd. Comprovantes', 'Valor Comprovado', '% Comprovado'];
        $json['consolidadoPorEtapa']['title'] = 'COMPROVAÇÃO CONSOLIDADA POR ETAPA';

        $maioresItensComprovados = $itens->maioresItensComprovados($idPronac);
        $itensAux = [];
        foreach($maioresItensComprovados as $k => $item){
            $itensAux[$k]['IdPRONAC'] = utf8_encode($item->IdPRONAC);
            $itensAux[$k]['Descricao'] = utf8_encode($item->Descricao);
            $itensAux[$k]['qtComprovantes'] = utf8_encode($item->qtComprovantes);
            $itensAux[$k]['vlComprovado'] = utf8_encode($item->vlComprovado);
            $itensAux[$k]['PercComprovado'] = utf8_encode($item->PercComprovado);
        }
        $json['maioresItensComprovados']['lines'] = $itensAux;
        $json['maioresItensComprovados']['cols'] = ['Produto', 'Qtd. Comprovantes', 'Valor Comprovado', '% Comprovado'];
        $json['maioresItensComprovados']['title'] = 'COMPROVAÇÃO CONSOLIDADA POR PRODUTO';

        $comprovacaoConsolidadaUfMunicipio = $itens->comprovacaoConsolidadaUfMunicipio($idPronac);
        $itensAux = [];
        foreach($comprovacaoConsolidadaUfMunicipio as $k => $item){
            $itensAux[$k]['IdPRONAC'] = utf8_encode($item->IdPRONAC);
            $itensAux[$k]['UF'] = utf8_encode($item->UF);
            $itensAux[$k]['qtComprovantes'] = utf8_encode($item->qtComprovantes);
            $itensAux[$k]['Municipio'] = utf8_encode($item->Municipio);
            $itensAux[$k]['vlComprovado'] = utf8_encode($item->vlComprovado);
            $itensAux[$k]['PercComprovado'] = utf8_encode($item->PercComprovado);
        }
        $json['comprovacaoConsolidadaUfMunicipio']['lines'] = $itensAux;
        $json['comprovacaoConsolidadaUfMunicipio']['cols'] = ['Produto', 'Qtd. Comprovantes', 'Valor Comprovado', '% Comprovado'];
        $json['comprovacaoConsolidadaUfMunicipio']['title'] = 'COMPROVAÇÃO CONSOLIDADA POR UF E MUNICIPIO';

        $maioresComprovacaoTipoDocumento = $itens->maioresComprovacaoTipoDocumento($idPronac);
        $itensAux = [];
        foreach($maioresComprovacaoTipoDocumento as $k => $item){
            $itensAux[$k]['IdPRONAC'] = utf8_encode($item->IdPRONAC);
            $itensAux[$k]['tpDocumento'] = utf8_encode($item->tpDocumento);
            $itensAux[$k]['nrComprovante'] = utf8_encode($item->nrComprovante);
            $itensAux[$k]['nmFornecedor'] = utf8_encode($item->nmFornecedor);
            $itensAux[$k]['qtComprovacoes'] = utf8_encode($item->qtComprovacoes);
            $itensAux[$k]['vlComprovado'] = utf8_encode($item->vlComprovado);
            $itensAux[$k]['PercComprovado'] = utf8_encode($item->PercComprovado);
        }
        $json['maioresComprovacaoTipoDocumento']['lines']= $itensAux;
        $json['maioresComprovacaoTipoDocumento']['cols'] = ['Produto', 'Qtd. Comprovantes', 'Valor Comprovado', '% Comprovado'];
        $json['maioresComprovacaoTipoDocumento']['title'] = 'MAIORES ITENS ORCÇAMENTÁRIOS COMPROVADO';

        $comprovacaoTipoDocumentoPagamento = $itens->comprovacaoTipoDocumentoPagamento($idPronac);
        $itensAux = [];
        foreach($comprovacaoTipoDocumentoPagamento as $k => $item){
            /* $itensAux[$k]['IdPRONAC'] = utf8_encode($item->IdPRONAC); */
            $itensAux[$k]['tpFormaDePagamento'] = utf8_encode($item->tpFormaDePagamento);
            $itensAux[$k]['nrDocumentoDePagamento'] = utf8_encode($item->nrDocumentoDePagamento);
            $itensAux[$k]['nmFornecedor'] = utf8_encode($item->nmFornecedor);
            $itensAux[$k]['qtComprovacoes'] = utf8_encode($item->qtComprovacoes);
            $itensAux[$k]['vlComprovado'] = utf8_encode($item->vlComprovado);
            $itensAux[$k]['PercComprovado'] = utf8_encode($item->PercComprovado);
        }
        $json['comprovacaoTipoDocumentoPagamento']['lines'] = $itensAux;
        $json['comprovacaoTipoDocumentoPagamento']['cols'] = ['Produto', 'Qtd. Comprovantes', 'Valor Comprovado', '% Comprovado'];
        $json['comprovacaoTipoDocumentoPagamento']['title'] = 'MAIORES COMPROVAÇÕES POR TIPO DE DOCUMENTOS DE PAGAMENTO';

        $maioresFornecedoresProjeto = $itens->maioresFornecedoresProjeto($idPronac);
        $itensAux = [];
        foreach($maioresFornecedoresProjeto as $k => $item){
            /* $itensAux[$k]['IdPRONAC'] = utf8_encode($item->IdPRONAC); */
            $itensAux[$k]['nrCNPJCPF'] = utf8_encode($item->nrCNPJCPF);
            $itensAux[$k]['nmFornecedor'] = utf8_encode($item->nmFornecedor);
            $itensAux[$k]['qtComprovacoes'] = utf8_encode($item->qtComprovacoes);
            $itensAux[$k]['vlComprovado'] = utf8_encode($item->vlComprovado);
            $itensAux[$k]['PercComprovado'] = utf8_encode($item->PercComprovado);
        }
        $json['maioresFornecedoresProjeto']['lines'] = $itensAux;
        $json['maioresFornecedoresProjeto']['cols'] = ['Produto', 'Qtd. Comprovantes', 'Valor Comprovado', '% Comprovado'];
        $json['maioresFornecedoresProjeto']['title'] = 'MAIORES POR FORNECEDORES DO PROJETO CULTURAL';

        $fornecedorItemProjeto = $itens->fornecedorItemProjeto($idPronac);
        foreach($fornecedorItemProjeto as $k => $item){
            /* $itensAux[$k]['IdPRONAC'] = utf8_encode($item->IdPRONAC); */
            $itensAux[$k]['nrCNPJCPF'] = utf8_encode($item->nrCNPJCPF);
            $itensAux[$k]['nmFornecedor'] = utf8_encode($item->nmFornecedor);
            $itensAux[$k]['Etapa'] = utf8_encode($item->Etapa);
            $itensAux[$k]['Item'] = utf8_encode($item->Item);
            $itensAux[$k]['vlComprovado'] = utf8_encode($item->vlComprovado);
            $itensAux[$k]['PercComprovado'] = utf8_encode($item->PercComprovado);
        }
        $json['maioresFornecedoresProjeto']['lines'] = $itensAux;
        $json['maioresFornecedoresProjeto']['cols'] = ['CNPJCPF', 'Qtd. Comprovantes', 'Valor Comprovado', '% Comprovado'];
        $json['maioresFornecedoresProjeto']['title'] = 'PROPONENTE FORNECEDOR DE ITEM PARA O PROJETO CULTURAL';

        $this->_helper->json($json);
    }
}
