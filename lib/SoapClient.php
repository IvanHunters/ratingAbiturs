<?php namespace Volsu\Soap;
use Volsu\Soap\SoapConfig;

class SoapClient
{
    /**
     * @var \SoapClient $request
     */
    private $request;
    /**
     * @var \StdClass $response
     */
    private $response;
    /**
     * @var string $error Error container
     */
    private $error;

    /**
     * SoapClient constructor.
     * @param SoapConfig $config
     * @param $method
     * @param array|null $parameters
     */
    public function __construct(SoapConfig $config, $method, array $parameters = null)
    {

        try
        {
            $this->request  = new \SoapClient($config->getUrl(), array(
                'login'     => $config->getLogin(),
                'password'  => $config->getPassword(),
                'keep_alive' => true,
				'trace' => true,
				'connection_timeout' => '100'
            ));

            $this->response = $this->request->$method($parameters);

        }
        catch (\SoapFault $error)
        {
            $this->error = $error->getCode().': '.$error->getMessage();
			//error_reporting(E_USER_ERROR);
			trigger_error($this->error, E_USER_ERROR);
			echo "test<br>";
            print_r($error);
    //         if(!$config->getDebug())
    //         {
				// header('Content-Type: text/html; charset=utf-8');
    //             echo '<div style="margin: 100px auto; border: red 3px solid;padding: 10px;"><h2>Произошел сбой работы программы. В настоящее время ведутся технические работы на сервере. Попробуйте позже. Если данное сообщение повторится - обратитесь за помощью по адресу: <a href="mailto:ovt@volsu.ru">ovt@volsu.ru</a></h2></div>';
				// exit();
    //         }
        }
    }

    /**
     * Return raw response by "return" field
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response->return;
    }

    /**
     * Return error container
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
