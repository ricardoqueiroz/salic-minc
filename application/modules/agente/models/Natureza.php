<?php

class Agente_Model_Natureza extends MinC_Db_Model
{
    protected $_idNatureza;
    protected $_idAgente;
    protected $_Direito;
    protected $_Esfera;
    protected $_Poder;
    protected $_Administracao;
    protected $_Usuario;

    /**
     * @return mixed
     */
    public function getIdNatureza()
    {
        return $this->_idNatureza;
    }

    /**
     * @param mixed $idNatureza
     */
    public function setIdNatureza($idNatureza)
    {
        $this->_idNatureza = $idNatureza;
    }

    /**
     * @return mixed
     */
    public function getIdAgente()
    {
        return $this->_idAgente;
    }

    /**
     * @param mixed $idAgente
     */
    public function setIdAgente($idAgente)
    {
        $this->_idAgente = $idAgente;
    }

    /**
     * @return mixed
     */
    public function getDireito()
    {
        return $this->_Direito;
    }

    /**
     * @param mixed $Direito
     */
    public function setDireito($Direito)
    {
        $this->_Direito = $Direito;
    }

    /**
     * @return mixed
     */
    public function getEsfera()
    {
        return $this->_Esfera;
    }

    /**
     * @param mixed $Esfera
     */
    public function setEsfera($Esfera)
    {
        $this->_Esfera = $Esfera;
    }

    /**
     * @return mixed
     */
    public function getPoder()
    {
        return $this->_Poder;
    }

    /**
     * @param mixed $Poder
     */
    public function setPoder($Poder)
    {
        $this->_Poder = $Poder;
    }

    /**
     * @return mixed
     */
    public function getAdministracao()
    {
        return $this->_Administracao;
    }

    /**
     * @param mixed $Administracao
     */
    public function setAdministracao($Administracao)
    {
        $this->_Administracao = $Administracao;
    }

    /**
     * @return mixed
     */
    public function getUsuario()
    {
        return $this->_Usuario;
    }

    /**
     * @param mixed $Usuario
     */
    public function setUsuario($Usuario)
    {
        $this->_Usuario = $Usuario;
    }
}
