<?php
importUtil('CollectionUtils.php');
importUtil('StringUtils.php');
importUtil('LoggerUtils.php');
importUtil('HttpMethodsEnum.php');

class Routes
{
	const CONTROLLER_ROUTE_INDEX = "controller";
	const METHOD_ROUTE_INDEX = "method";
	const VERB_ROUTE_INDEX = "verb";
	const PARAMS_ROUTE_INDEX = "params";
    const VALIDATOR_ROUTE_INDEX = "validator";
    const BEAN_ROUTE_INDEX = "bean";
	
	private static $instance = null;
		
	public $routes = array();
    
    private $logger;
	
	private function __construct(){
        $this->logger = LoggerUtils::getLogger();
       
		//add default routes
		$this->addRoute("/home", "acceuil/Home", "index", HttpMethodsEnum::GET);
        
        /**
         * Register
         */
        $this->addRoute("/register", "account/Register", "index", HttpMethodsEnum::GET);
		$this->addRoute("/register", "account/Register", "register", HttpMethodsEnum::POST, null, "account/Register", "player/PlayerBean");
        
        /**
         * Connection
         */
		$this->addRoute("/authentication", "auth/Auth", "index", HttpMethodsEnum::GET);
        $this->addRoute("/authentication", "auth/Auth", "authentify", HttpMethodsEnum::POST, null, "auth/Auth");
        
        /**
         * Address
         */
        $this->addRoute("/address/list", "address/Address", "index", HttpMethodsEnum::GET);
        $this->addRoute("/address/add", "address/Address", "addPage", HttpMethodsEnum::GET);
        $this->addRoute("/address/add", "address/Address", "add", HttpMethodsEnum::POST, null, "address/AddAddress");
				
		/**
		 * exemple de routes avec des paramètres
		 */
		//$this->addRoute("/home/([0-9]+)/test/([0-9]+)", "acceuil/Home", "index", "GET", array("idToto", "idTiti"));
        
        /**
		 * exemple de routes avec un validator
		 */
		//$this->addRoute("/contact/mail", "contact/Contact", "sendMail", HttpMethodsEnum::POST, null, "contact/ContactSendMail");
		
        /**
         * exemple de route avec un validator basé sur la validation par annotation sur le bean
         */
        //$this->addRoute("/register", "account/Register", "register", HttpMethodsEnum::POST, null, "account/Register", "player/PlayerBean");
        
        /**
         * exemple de route avec une validation automatique basée sur les annotations du bean
         */
        //$this->addRoute("/register", "account/Register", "register", HttpMethodsEnum::POST, null, null, "player/PlayerBean");
	}
	
	/**
	 * Add a route for REST routing
	 * 
	 * @param string $url : url pattern
	 * @param string $controller : controller name
	 * @param string $controllerMethod : controller executed method
	 * @param [string $verb] : Http verb (GET/POST)
	 * @param [Array $params] : Url parameters if the contains parameters in the url pattern
	 * @param [string $validator] : validator name executed on this request
	 * @param [string $bean] : bean name used by this request. If this specified and $validator is null then
	 *                         automatic validation based on bean annotations is executed 
	 */
	public function addRoute($url, $controller, $controllerMethod, $verb = HttpMethodsEnum::GET, Array $params = null, $validator = null, $bean = null)
	{
		if(StringUtils::isBlank($verb))
		{
			$verb = HttpMethodsEnum::GET;
		}
		
		$this->routes[$url . ':' . $verb] = array(
				self::CONTROLLER_ROUTE_INDEX => $controller,
				self::METHOD_ROUTE_INDEX => $controllerMethod,
				self::VERB_ROUTE_INDEX => $verb,
				self::PARAMS_ROUTE_INDEX => $params,
                self::VALIDATOR_ROUTE_INDEX => $validator,
		        self::BEAN_ROUTE_INDEX => $bean
		);
	}
	
	/**
	 * Match an url with the routes table
	 * and return an array with elements
	 * 
	 * @param string $url
	 * @return Array representing route or null if route not matched
	 */
	public function match($url)
	{
		$allRouteUrl = array_keys($this->routes);
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        $this->logger->debug("match rest url '$url' with http method $requestMethod");
        $url .= ':' . $requestMethod;
        
		if(CollectionUtils::isNotEmpty($allRouteUrl) && !StringUtils::isBlank($url))
		{
			foreach ($this->routes as $route => $config)
			{
				if(preg_match("#^" . $route . "$#", $url, $params))
				{
					$usedMethod = strtoupper($_SERVER['REQUEST_METHOD']);
					if($usedMethod === HttpMethodsEnum::GET)
					{
						$configParamNames = $config[self::PARAMS_ROUTE_INDEX];
						//build associative params array
						if(CollectionUtils::isNotEmpty($configParamNames))
						{
							$paramsFromUrl = array_slice($params, 1);
							$params = array();
							foreach ($configParamNames as $index => $paramName)
							{
								$params[$paramName] = $paramsFromUrl[$index];
							}
							
							$config[self::PARAMS_ROUTE_INDEX] = $params;
						}
					}
					else if($usedMethod === HttpMethodsEnum::POST)
					{
						$config[self::PARAMS_ROUTE_INDEX] = $_POST;
					}
					
					//xss and sql injection protection
					if(CollectionUtils::isNotEmpty($config[self::PARAMS_ROUTE_INDEX]))
					{
						$config[self::PARAMS_ROUTE_INDEX] = $this->cleanInputs($config[self::PARAMS_ROUTE_INDEX]);
					}
					
					return $config;
				}
			}
		}
		
		return null;
	}
	
	/**
	 * Clean input data
	 * 
	 * @param unknown $data
	 * @return multitype:NULL 
	 */
	private function cleanInputs($data){
		$clean_input = array();
		if(is_array($data)){
			foreach($data as $key => $value){
				$clean_input[$key] = $this->cleanInputs($value);
			}
		}else{			
			$data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, "UTF-8", false);
            if(!get_magic_quotes_gpc()){
				$data = trim(addslashes($data));
			}
			$clean_input = trim($data);
		}
		return $clean_input;
	}

	/**
	 * Récupère l'instance de la classe
	 *
	 * @return Routes
	 */
	public static function getInstance()
	{
		if(is_null(self::$instance))
		{
			self::$instance = new self();
		}
		
		return self::$instance;
	}
}
?>