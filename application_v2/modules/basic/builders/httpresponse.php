<?php

    /**
     * Basic_HttpResponse_Builder class file.
     *
     
     */
    
    
    // Load required classes
    ClassLoader::loadCoreClass('notfoundexception');
    ClassLoader::loadBuilderClass('basic.abstract');
    ClassLoader::loadModelClass('basic.htmlresponse');
    
    
    /**
     * HTML response builder
     *
     
     */
    class Basic_HttpResponse_Builder extends Basic_Abstract_Builder
    {
        public function getByRequest(Request $_request = NULL)
        {
            $response = new Basic_HtmlResponse_Model();
            
            try
            {
                // Extract request URL
                $url = $_request->getUrl();
                
                
                // Extract first URL token
                $firstUrlToken = ($p = strpos($url, '/')) ? substr($url, 0, $p) : substr($url, 0);
                
                
                if ($firstUrlToken == 'async') // JSON (asynchronous) request
                {
                    // Load async action exception class
                    ClassLoader::loadCoreClass('asyncactionexception');
            
            
                    // Create async action object
                    $restUrl = ($t = substr($url, strlen($firstUrlToken)+1)) ? $t : '';
                    $asyncActionRef = ($p = strpos($restUrl, '/')) ? substr($restUrl, 0, $p) : substr($restUrl, 0);
                    ClassLoader::loadAsyncActionClass($asyncActionRef);
                    list($moduleKey, $asyncActionKey) = explode('.', $asyncActionRef);
                    $asyncActionClassName = $moduleKey.'_'.$asyncActionKey.'_AsyncAction';
                    $action = new $asyncActionClassName();
                    
                    
                    // Set response properties
                    $response->messageBodyFormat = $response->dataType = 'json';
                    if ($asyncActionRef == 'basic.fileupload') $response->dataType = 'html'; //! Old IE uses iframe for async file upload
            
                    // Response success by default
                    $response->messageBody = new stdClass();
                    $response->messageBody->success = true;
                    $response->messageBody->message = 'OK';
                    
                    
                    try
                    {
                        //! Ensure action privileges
                        
                        
                        // Perform the action
                        $action->perform($_request->getDomainId(), $_request->getParams());
                    
                        
                        // Set the response data
                        $data = $action->getData();
                        if (count($data)) $response->messageBody->data = $data;
                    }
                    
                    catch (AsyncActionAuthException $e)
                    {
                        $response->messageBody->success = false;
                        $response->messageBody->auth = false;
                        $response->messageBody->message = 'You are not authorized to perform this action.';
                    }
                    
                    catch (AsyncActionSessionExpireException $e)
                    {
                        $response->messageBody->success = false;
                        $response->messageBody->expired = true;
                        $response->messageBody->message = 'Session has been expired.';
                    }
                    
                    catch (AsyncActionException $e)
                    {
                        $response->messageBody->success = false;
                        $response->messageBody->message = $e->getMessage();
                        
                        
                        // Set the response errors
                        $errors = $action->getErrors();
                        if (count($errors))
                            $response->messageBody->errors = $errors;
                    }
                }
                
                else // HTML request
                {
                    // Extract request URL
                    $url = $_request->getUrl();
                        
                        
                    // Skip site path
                    if (strlen(SITE_PATH))
                    {
                        $skipUrlToken = ($p = strpos($url, '/')) ? substr($url, 0, $p) : substr($url, 0);
                        $url = ($t = substr($url, strlen($skipUrlToken)+1)) ? $t : '';
                    }
                    
                    
                    // Extract first URL token
                    $firstUrlToken = ($p = strpos($url, '/')) ? substr($url, 0, $p) : substr($url, 0);
                    
                    
                    if ($firstUrlToken == 'backend')
                    {
                        $response->messageBody = new ModelViewPair(
                            Application::getBuilder('backend.htmldocument')->getByRequest($_request),
                            'backend.htmldocument'
                        );
                    }
                    else
                    {
                        $response->messageBody = new ModelViewPair(
                            Application::getBuilder('main.htmldocument')->getByRequest($_request),
                            'main.htmldocument'
                        );
                    }
                }
            }
            
            catch (NotFoundException $e)
            {
                $response->statusCode = 404;
                $response->dataType = 'html';
                $response->messageBody = new ModelViewPair(
                    null,
                    'basic.notfoundhtmldocument'
                );
            }
            
            catch (Exception $e)
            {
                echo '<h1>Exception</h1>';
                echo '<p>'.$e->getMessage().'</p>';
                echo '<pre>'.$e->getTraceAsString().'</pre>';
                exit();
                // $response->statusCode = 500;
                // $response->dataType = 'html';
                // $response->model = GeneratorsFactory::getGenerator('basic.exceptionhtmldocument')->get(array('exception' => $e));
                // $response->view = 'basic.exceptionhtmldocument';
            }
            
            return $response;
        }
    }

?>