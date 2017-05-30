<?php

namespace Tayron;

use \Exception;
use \InvalidArgumentException;

/**
 * Classe que gerencia os dados na sessão.
 *
 * @author Tayron Miranda <dev@tayron.com.br>
 */
class Session implements \SessionHandlerInterface 
{
    /**
     * Armazena a instancia de Sessao
     *
     * @var Session
     */
    private static $instance;

    /**
     * Session::__construct
     *
     * Impede com que o objeto seja instanciado
     */
    final private function __construct() 
    {
    }

    /**
     * Session::__clone
     *
     * Impede que a classe Requisição seja clonada
     *
     * @throws Exception Lança execção caso o usuário tente clonar este classe
     *
     * @return void
     */
    final public function __clone() 
    {
        throw new Exception('A classe Requisicao não pode ser clonada.');
    }

    /**
     * Session::__wakeup
     *
     * Impede que a classe Requisição execute __wakeup
     *
     * @throws Exception Lança execção caso o usuário tente executar este método
     *
     * @return void
     */
    final public function __wakeup() 
    {
        throw new Exception('A classe Requisicao não pode executar __wakeup.');
    }

    /**
     * Session::getInstancia
     *
     * Retorna uma instância única de uma classe.
     *
     * @param int $maxlifetime Tempo de expiração da sessão em minutos
     *
     * @return Session Instancia única de Sessão
     */
    public static function getInstance($maxlifetime = 1) 
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        self::$instance->setMaxLifeTime($maxlifetime);
        self::$instance->open(null, null);
        return self::$instance;
    }
    
    /**
     * Session::setMaxLifeTime
     *
     * Inicializa sessao
     *
     * @param int $maxlifetime Tempo de expiração da sessão em minutos
     * @param string $name Nome da sesssão
     */    
    private function setMaxLifeTime($maxlifetime)
    {
        if (empty($maxlifetime)) {
            throw new InvalidArgumentException('Deve-se informar o tempo de expiração da sessão');
        }        
        $this->maxlifetime = (int)$maxlifetime;
    }

    /**
     * Session::open
     *
     * Inicializa sessao
     *
     * @param stirng $savePath Local onde a ssessão deve ser armazenada
     * @param string $name Nome da sesssão
     */
    public function open($savePath, $name) 
    {
        session_start();

        if (!$this->debug()) {
            $this->write('session_id', session_id());
            $this->write('session_start', new \DateTime('now'));
        }
    }

    /**
     * Session::gc
     *
     * Método que verifica o tempo limite para expiração da sessão e o atualiza,
     * caso o tempo já tenha passado a sessão é destruida
     *
     * @param int $maxlifetime Tempo de expiração da sessão em minutos
     *
     * @return boolean Retorna true em caso de sucesso
     */
    public function gc($maxlifetime) 
    {
        $sessionStart = $this->read('session_start');
        $dateTimeNow = new \DateTime('now');
        $time = $dateTimeNow->diff($sessionStart);

        ($time->i >= $maxlifetime) ? $this->close() : $this->renew();

        return true;
    }

    /**
     * Session::write
     *
     * Método que seta os dados na sessão
     *
     * @param string $sessionId Indenfificador da sessão
     * @param mixed $sessionData Valor a ser armazenado na sessão
     *
     * @return boolean Retorna true em caso de sucesso
     */
    public function write($sessionId, $sessionData) 
    {   
        if (empty($sessionId)) {
            throw new InvalidArgumentException('Deve-se informar um indentificador para o valor a ser armazenado');
        }

        if (empty($sessionData)) {
            throw new InvalidArgumentException('Deve-se informar um valor para o indentificador informado');
        }

        $listSessionId = explode('.', $sessionId);
        $countKeys = count($listSessionId);
        
        if($countKeys == 2){
            $_SESSION[$listSessionId[0]][$listSessionId[1]] = $sessionData;
        }else if($countKeys == 2){
            $_SESSION[$listSessionId[0]][$listSessionId[1]][$listSessionId[2]] = $sessionData;
        }else{        
            $_SESSION[$sessionId] = $sessionData;
        }

        return ($this->read($sessionId)) ? true : false;
    }

    /**
     * Session::read
     *
     * Método que retorna um dado na sessão
     *
     * @param string $sessionId Indenfificador da sessão
     * @return mixed Retorna o conteúdo de um indenfificador na sessão
     */
    public function read($sessionId) 
    {
        $listSessionId = explode('.', $sessionId);
        $countKeys = count($listSessionId);
        
        if($countKeys == 2){
            return (isset($_SESSION[$listSessionId[0]][$listSessionId[1]])) 
                ? $_SESSION[$listSessionId[0]][$listSessionId[1]] 
                : null;
        }else if($countKeys == 3){
            return (isset($_SESSION[$listSessionId[0]][$listSessionId[1]][$listSessionId[2]])) 
                ? $_SESSION[$listSessionId[0]][$listSessionId[1]][$listSessionId[2]] 
                : null;            
        }else{        
            return (isset($_SESSION[$sessionId])) ? $_SESSION[$sessionId] : null;
        }        
    }

    /**
     * Session::destroy
     *
     * Método que remove um item da sessão
     *
     * @param string $sessionId Indenfificador da sessão
     * @return boolean Retorna true em caso de sucesso
     */
    public function destroy($sessionId) 
    {
        if (empty($sessionId)) {
            throw new InvalidArgumentException('Deve-se informar um indentificador para remover o valor armazenado');
        }

        unset($_SESSION[$sessionId]);
        return ($this->read($sessionId)) ? false : true;
    }

    /**
     * Session::clear
     *
     * Método que limpa todos os registros da sessao
     *
     * @return void
     */
    public function clear() 
    {
        $_SESSION = array();
    }

    /**
     * Session::close
     *
     * Método que fecha a sessão
     *
     * @return bool Retorna true em caso de sucesso
     */
    public function close() 
    {
        session_destroy();
        $_SESSION = array();
        return (!$_SESSION) ? true : false;
    }

    /**
     * Session::isRegistered
     *
     * Métpdp que verifica se a sessão está registrada
     *
     * @return boolean
     */
    public function isRegistered() 
    {
        return $this->get('session_id') ? true : false;
    }

    /**
     * Session::getSessionId
     *
     * Método que retorna o Id da sessão
     *
     * @return integer Id da sessão
     */
    public function getSessionId() 
    {
        return $this->get('session_id');
    }

    /**
     * Session::debug
     *
     * Método que retorna todos os dados do sessão
     *
     * @return void
     */
    public function debug() 
    {
        return $_SESSION;
    }

    /**
     * Session::renew
     *
     * Método que atualiza o tempo de inicio da sessão
     *
     * @return boolean
     */
    private function renew() 
    {
        $this->write('session_start', new \DateTime('now'));
    }
}
