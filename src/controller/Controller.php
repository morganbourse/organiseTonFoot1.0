<?php
importUtil('CollectionUtils.php');
importUtil('StringUtils.php');
importUtil('MapperUtils.php');
importUtil('JsonUtils.php');
importUtil('HeaderUtils.php');
importUtil('LoggerUtils.php');
importUtil('TplEngineUtils.php');
importController('ControllerException.php');

/**
 * Base Controller
 * 
 * @author Morgan
 *        
 */
abstract class Controller {
    protected $logger;
    
    /**
     * Constructor
     */
    public function Controller() {
        $this->logger = LoggerUtils::getLogger ();
    }
    
    /**
     * draw the template in the response
     * 
     * @param String $templateName            
     * @param array $variables            
     * @throws ControllerException
     */
    public function draw($templateName, Array $variables = null) {
        try {
            TplEngineUtils::renderTpl ( $templateName, $variables, false );
        }
        catch ( Exception $ex ) {
            throw new ControllerException ( $ex->getMessage (), $ex->getCode (), $ex->getPrevious () );
        }
    }
    
    /**
     * get the template as string
     * 
     * @param String $templateName            
     * @param array $variables            
     * @throws ControllerException
     */
    public function getTemplateAsString($templateName, Array $variables = null) {
        try {
            return TplEngineUtils::renderTpl ( $templateName, $variables, true );
        }
        catch ( Exception $ex ) {
            throw new ControllerException ( $ex->getMessage (), $ex->getCode (), $ex->getPrevious () );
        }
    }
    
    /**
     * renderJson
     *
     * @param array $jsonArray            
     */
    public function renderJson(Array $jsonArray, $httpCode = 200) {
        JsonUtils::renderJson ( $jsonArray, $httpCode );
    }
        
    /**
     * Default controller function
     */
    abstract function index();
}
?>