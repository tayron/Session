## Session

Classe para manipulação de sessão no PHP


## Recursos
  - write($sessionId, $sessionData) - Seta um item na sessão, deve-se informar sua chave e valor    
  - read($sessionId) - Retorna um item da sessão através de sua chave    
  - destroy($sessionId) - Remove um item da sessão através da sua chave
  - clear() - Limpa todos os registros da ssessão
  - close() - Destroi a sessão
  - isRegistered() - Verifica se a sessão possui session_id
  - getSessionId() - Retorna session_id da sessão
  - debug() - Retorna tudo que há na sessão



## Utilização via composer

```sh
    "require": {
        ...
        "tayron/session" : "dev-master"
        ... 
    },    
```
