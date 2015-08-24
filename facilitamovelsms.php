<?php

/*
 * classe de envio de SMS usando a solucao da Facilita Movel (www.facilitamovel.com.br)
 *
 * Desenvolvido por rod@wgo.com.br
 * 24/08/2015
 */

class FacilitaMovelSms
{
    /**
     * Usuario da conta Facilita Movel.
     *
     * @var string
     */
    protected $username = '';

    /**
     * Senha da conta Facilita Movel.
     *
     * @var string
     */
    protected $password = '';

    /**
     * Endereco/URL principal de acesso ao webservice da Facilita Movel.
     *
     * @var string
     */
    protected $endereco = 'https://www.facilitamovel.com.br/api/simpleSend.ft?';

    /**
     * codigo de resposta da chamada ao webservice.
     *
     * @var string
     */
    protected $responseCode = null;

    /**
     * mensagem de resposta da chamada ao webservice.
     *
     * @var string
     */
    protected $responseMsg = '';

    /**
     * curl handler para a requisicao.
     *
     * @var resource
     */
    protected $curl = null;

    /**
     * tamanho maximo para a mensagem a ser enviada via SMS.
     *
     * @var int
     */
    const TAMANHO_MAXIMO_MENSAGEM = 160;

    public function __construct($username, $password, $endereco = 'https://www.facilitamovel.com.br/api/simpleSend.ft?')
    {
        $this->username = $username;
        $this->password = $password;
        $this->endereco = $endereco;

        $this->curl = curl_init();
    }

    /**
     * envia uma mensagem via SMS ao telefone celular do destinatario.
     *
     * @param string $destinatario telefone do destinatario
     * @param string $mensagem     mensagem a ser enviada
     *
     * @throws Exception dispara excecao caso a mensagem a ser enviada possua tamanho superior a TAMANHO_MAXIMO_MENSAGEM
     *
     * @return string referente ao ID da mensagem enviada; false caso houve algum erro no envio
     */
    public function send_sms($destinatario, $mensagem, $remetente = '', $data = '')
    {
        if (strlen($mensagem) > self::TAMANHO_MAXIMO_MENSAGEM) {
            throw new Exception('Tamanho da mensagem nao pode ultrapassar '.self::TAMANHO_MAXIMO_MENSAGEM.' caracteres.');
        }

        return $this->exec($destinatario, $mensagem);
    }

    public function getIdMensagem()
    {
        if ('5' == $this->responseCode || '6' == $this->responseCode) {
            return $this->responseMsg;
        }

        return false;
    }

    public function getCodigoResposta()
    {
        return $this->responseCode;
    }

    public function getMensagemResposta()
    {
        return $this->responseMsg;
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }

    private function exec($destinatario, $mensagem)
    {
        $this->buildUrl($destinatario, $mensagem);
        $options = $this->buildCurlOptions();

        curl_setopt_array($this->curl, $options);
        $response = curl_exec($this->curl);

        // verifica se houve erro ao executar curl
        if ($errno = curl_errno($this->curl)) {
            throw new Exception(sprintf('curl error %d: %s', $errno, curl_error($this->curl)));
        }

        // execucao curl ok, verifica se a requisicao http tambem retorna OK
        if (($httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE)) != '200') {
            throw new Exception(sprintf('Response code of requested resource is %d and it *must* be 200', $httpCode));
        }

        $this->parseResponse($response);

        return $this->getIdMensagem();
    }

    private function buildUrl($destinatario, $mensagem)
    {
        $query = array(
                    'user' => $this->username,
                    'password' => $this->password,
                    'destinatario' => $destinatario,
                    'msg' => $mensagem,
                );

        $this->url = $this->endereco.http_build_query($query);
    }

    private function buildCurlOptions()
    {
        $options = array(
                    CURLOPT_URL => $this->url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                );

        return $options;
    }

    private function parseResponse($response)
    {
        list($this->responseCode, $this->responseMsg) = explode(';', $response);
    }
} // FacilitaMovel

