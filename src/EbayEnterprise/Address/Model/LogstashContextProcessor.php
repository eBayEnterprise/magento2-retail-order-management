<?php

namespace EbayEnterprise\Address\Model;

class LogstashContextProcessor
{
    /** @var LoggerContext */
    protected $logContext;

    /**
     * @param LoggerContext
     */
    public function __construct(
        LoggerContext $logContext
    ) {
        $this->logContext = $logContext;
    }

    public function __invoke(array $record)
    {
        $record['context'] = array_merge($record['context'], $this->logContext->buildContext());
        return $record;
    }
}
