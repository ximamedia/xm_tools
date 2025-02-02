<?php

namespace Xima\XmTools\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class TtContent
 *
 * Model of tt_content
 *
 * @package Xima\XmTools\Domain\Model
 */
class TtContent extends AbstractEntity
{

    /**
     * uid
     *
     * @var string
     */
    protected $uid = '';

    /**
     * pid
     *
     * @var string
     */
    protected $pid = '';

    /**
     * header
     *
     * @var string
     */
    protected $header = '';

    /**
     * sorting
     *
     * @var string
     */
    protected $sorting = '';

    /**
     * contentType
     *
     * @var string
     */
    protected $contentType = '';

    /**
     * @var string
     */
    protected $piFlexform;

    /**
     * @var string
     */
    protected $listType;

    /**
     * @var int
     */
    protected $sysLanguageUid;

    /**
     * Gets the uid
     *
     * @return string $uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Gets the pid
     *
     * @return string $pid
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Returns the header
     *
     * @return string $header
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Sets the header
     *
     * @param string $header
     * @return void
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * Returns the sorting
     *
     * @return string $sorting
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * Sets the sorting
     *
     * @param string $sorting
     * @return void
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }

    /**
     * Returns the contentType
     *
     * @return string $contentType
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Sets the contentType
     *
     * @param string $contentType
     * @return void
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }


    /**
     * @return string
     */
    public function getPiFlexform()
    {
        return $this->piFlexform;
    }

    /**
     * @return string
     */
    public function getListType()
    {
        return $this->listType;
    }

    /**
     * @return int
     */
    public function getSysLanguageUid()
    {
        return $this->sysLanguageUid;
    }

    /**
     * @param int $sysLanguageUid
     */
    public function setSysLanguageUid($sysLanguageUid)
    {
        $this->sysLanguageUid = $sysLanguageUid;
    }

}
