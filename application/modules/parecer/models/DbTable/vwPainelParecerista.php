<?php

class Parecer_Model_DbTable_vwPainelParecerista extends MinC_Db_Table_Abstract
{
    protected $_schema    = 'sac';
    protected $_name      = 'vwPainelParecerista';

    public function listar($where = array(), $order = array(), $start = 0, $limit = 10, $search = null)
    {
        $db = $this->getAdapter();
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);

        $sql = $db->select()
            ->from($this->_name, '*', $this->_schema);

        foreach ($where as $coluna=>$valor)
        {
            $sql->where($coluna, $valor);
        }

        if (!empty($search['value'])) {
            $sql->where('PRONAC like ? OR NomeProjeto like ?', '%'.$search['value'].'%');
        }

        $sql->order($order);

        if (!is_null($start) && $limit) {
            $start = (int)$start;
            $limit = (int)$limit;
            $sql->limitPage($start, $limit);
        }

        return $db->fetchAll($sql);
    }

    public function listarTotal($where = array(), $order = array(), $start = null, $limit = null, $search = null)
    {
        $db = $this->getAdapter();
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);

        $sql = $db->select()
            ->from($this->_name, 'count(*) as total', $this->_schema)
        ;

        foreach ($where as $coluna=>$valor)
        {
            $sql->where($coluna, $valor);
        }

        if (!empty($search['value'])) {
            $sql->where('PRONAC like ? OR NomeProjeto like ?', '%'.$search['value'].'%');
        }

        if (!is_null($start) && $limit) {
            $start = (int)$start;
            $limit = (int)$limit;
            $sql->limitPage($start, $limit);
        }

        return $db->fetchOne($sql);
    }

}

