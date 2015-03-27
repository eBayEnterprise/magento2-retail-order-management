<?php

namespace EbayEnterprise\Address\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;

class LoggerContext
{
    const APP_CONTEXT = 'app_context';
    const APP_NAME = 'app_name';
    const APP_REQUEST_URL = 'app_request_url';
    const DATA_CENTER = 'data_center';
    const EXCEPTION_CLASS = 'exception_class';
    const EXCEPTION_MESSAGE = 'exception_message';
    const EXCEPTION_STACKTRACE = 'exception_stacktrace';
    const HOST = 'host';
    const LOG_TYPE = 'log_type';
    const MESSAGE = 'message';
    const RESOURCE = 'resource';
    const ROM_REQUEST_URL = 'rom_request_url';
    const ROM_REQUEST_HEADER = 'rom_request_header';
    const ROM_REQUEST_BODY = 'rom_request_body';
    const ROM_RESPONSE_HEADER = 'rom_response_header';
    const ROM_RESPONSE_BODY = 'rom_response_body';
    const SESSION_ID = 'session_id';

    /** @var ScopeConfigInterface */
    protected $scopeConfig;
    /** @var UrlInterface */
    protected $url;
    /** @var SessionManagerInterface */
    protected $session;
    /** @var string */
    protected $safeSessionId;

    /**
     * @param ScopeConfigInterface
     * @param UrlInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        SessionManagerInterface $session,
        $moduleName
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
        $this->session = $session;
        $this->moduleName = $moduleName;
    }

    public function buildContext()
    {
        return array_merge(
            // @TODO most of these values are probably not quite accurate
            [
                self::APP_CONTEXT => 'php',
                self::APP_NAME => 'Magento Webstore',
                self::DATA_CENTER => 'external',
                self::HOST => $this->url->getBaseUrl(),
                self::LOG_TYPE => 'system',
                self::RESOURCE => $this->moduleName,
            ],
            $this->getOptionalContext()
        );
    }

    /**
     * Get context values that may not be available and are not required
     * for every log call. Only available values will be included in this
     * set of context values.
     *
     * @return array
     */
    protected function getOptionalContext()
    {
        return array_filter([
            self::APP_REQUEST_URL => $this->url->getCurrentUrl(),
            self::SESSION_ID => $this->getSafeSessionId(),
        ]);
    }

    /**
     * Get a hash of the session id.
     *
     * @return string|null
     */
    protected function getSafeSessionId()
    {
        if (!$this->safeSessionId) {
            // When there is no session id, safeSessionId will be set to null.
            // This will prevent the method call from getting cached (above
            // check will fail) and cause the session to be rechecked for a
            // session id on successive calls until a session id is retrieved.
            // Allowing this behavior in case a log call happens before the
            // session is started. Some calls may miss the session id but once
            // it is available, it will be retrieved, cached and attached to
            // later log calls.
            $sessionId = $this->session->getSessionId();
            $this->safeSessionId = $sessionId ? hash('sha256', $this->session->getSessionId()) : null;
        }
        return $this->safeSessionId;
    }
}
