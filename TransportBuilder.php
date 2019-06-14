<?php

namespace Bd\Report\Model\Mail;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder {

    /**
     * @var \Bd\Report\Mail\Message
     */
    protected $message;

    /**
     * Add an attachment to the message.
     *
     * @param string $content
     * @param string $fileName
     * @param string $fileType
     * @return $this
     */
    public function addAttachment($content, $fileName, $fileType) {
        $this->message->createAttachment(
            $fileName,
            'text/csv',
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $attachedName
        );
        return $this;
    }

    /**
     * After all parts are set, add them to message body.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareMessage() {
        parent::prepareMessage();

        $this->message->setPartsToBody();

        return $this;
    }

}
