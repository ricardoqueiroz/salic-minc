<?php

/**
 * @name Agente_Model_TbMensagemProjetoMapper
 * @package Modules/Admissibilidade
 * @subpackage Models
 *
 * @author Ruy Junior Ferreira Silva <ruyjfs@gmail.com>
 * @since 01/10/2017
 *
 * @copyright © 2012 - Ministerio da Cultura - Todos os direitos reservados.
 * @link http://salic.cultura.gov.br
 */
class Projeto_Model_TbHomologacaoMapper extends MinC_Db_Mapper
{

    public function __construct()
    {
        $this->setDbTable('Projeto_Model_DbTable_TbHomologacao');
    }

    public function isUniqueCpfCnpj($value)
    {
        return ($this->findBy(array("cnpjcpf" => $value))) ? true : false;
    }

    public function isValid($model)
    {
        $booStatus = true;
        $arrData = $model->toArray();
        $arrRequired = array(
            'idPronac',
            'tpHomologacao',
            'dsHomologacao',
        );
        foreach ($arrRequired as $strValue) {
            if (!isset($arrData[$strValue]) || empty($arrData[$strValue])) {
                $this->setMessage('Campo obrigat&oacute;rio!', $strValue);
                $booStatus = false;
            }
        }
        return $booStatus;
    }

    public function salvar($arrData)
    {
        $booStatus = false;
        if (!empty($arrData)) {
            $model = new Projeto_Model_TbHomologacao($arrData);
            try {
                $auth = Zend_Auth::getInstance(); // pega a autenticacao
                $arrAuth = array_change_key_case((array)$auth->getIdentity());
                $grupoAtivo = new Zend_Session_Namespace('GrupoAtivo');
                $intUsuOrgao = $grupoAtivo->codOrgao;
                //$intUsuOrgao = $grupoAtivo->codGrupo;
                //var_dump($intUsuOrgao, $grupoAtivo->codOrgao);die;
                $model->setDtHomologacao(date('Y-m-d h:i:s'));
                $model->setIdUsuario($arrAuth['usu_codigo']);

                echo '<pre>';
                var_dump('$model');
                exit;
//                $model->setIdRemetenteUnidade($intUsuOrgao);
//                $model->setIdDestinatario($arrAuth['usu_codigo']);
//                $model->setCdTipoMensagem('E');
//                $model->setStAtivo(1);
                if ($intId = parent::save($model)) {
                    $booStatus = 1;
                    $this->setMessage('Pergunta enviada com sucesso!');
                } else {
                    $this->setMessage('Nao foi possivel enviar mensagem!');
                }
            } catch (Exception $e) {
                $this->setMessage($e->getMessage());
            }
        }
        return $booStatus;
    }

    public function save($arrData)
    {
        $booStatus = false;
        if (!empty($arrData)) {
            $model = new Projeto_Model_TbHomologacao($arrData);
            try {
                $auth = Zend_Auth::getInstance(); // pega a autenticacao
                $arrAuth = array_change_key_case((array)$auth->getIdentity());
                if (!isset($arrData['idHomologacao']) || empty($arrData['idHomologacao'])) {
                    $model->setDtHomologacao(date('Y-m-d h:i:s'));
                }
                $model->setIdUsuario($arrAuth['usu_codigo']);
                if ($intId = parent::save($model)) {
                    $booStatus = 1;
                    $this->setMessage('Salvo com sucesso!');
                } else {
                    $this->setMessage('Nao foi possivel salvar!');
                }
            } catch (Exception $e) {
                $this->setMessage($e->getMessage());
            }
        }
        return $booStatus;
    }
}
