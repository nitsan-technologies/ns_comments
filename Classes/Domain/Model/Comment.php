<?php

namespace Nitsan\NsComments\Domain\Model;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2023
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Comment
 */
class Comment extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var int<-1, max>|null The uid of the language of the object. This is the id of the corresponding sing language.
     *
     * @internal
     * @todo make private in 13.0 and expose value via getter
     */
    protected int|null $_languageUid = null;


    /**
     * crdate as unix timestamp
     *
     * @var int
     */
    protected $crdate = 0;

    /**
     * hidden
     *
     * @var int
     */
    protected $hidden = 0;

    /**
     * feuserid
     *
     * @var int
     */
    protected $feuserid = 0;

    /**
     * username
     *
     * @var string
     */
    protected $username = '';

    /**
     * userimage
     *
     * @var string
     */
    protected $userimage = '';

    /**
     * usermail
     *
     * @var string
     */
    protected $usermail = '';

    /**
     * captcha
     *
     * @var string
     */
    protected $captcha = '';

    /**
     * paramlink
     *
     * @var string
     */
    protected $paramlink = '';

    /**
     * pageuid
     *
     * @var int
     */
    protected $pageuid = 0;

    /**
     * accesstoken
     *
     * @var string
     */
    protected $accesstoken;

    /**
     * description
     *
     * @var string
     */
    protected $description = '';

    /**
     * childcomment
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Nitsan\NsComments\Domain\Model\Comment>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $childcomment = null;

    /**
     * terms
     *
     * @var bool
     */
    protected $terms = false;

    /**
     * @param int $_languageUid
     * @return void
     */
    public function set_languageUid($_languageUid)
    {
        $this->_languageUid = $_languageUid;
    }

    /**
     * @return int
     */
    public function get_languageUid()
    {
        return $this->_languageUid;
    }

    /**
     * Returns the pageuid
     *
     * @return int $pageuid
     */
    public function getPageuid()
    {
        return $this->pageuid;
    }

    /**
     * Sets the pageuid
     *
     * @param int $pageuid
     * @return void
     */
    public function setPageuid($pageuid)
    {
        $this->pageuid = $pageuid;
    }

    /**
     * Returns the accesstoken
     *
     * @return string $accesstoken
     */
    public function getAccesstoken()
    {
        return $this->accesstoken;
    }

    /**
     * Sets the accesstoken
     *
     * @param string $accesstoken
     * @return void
     */
    public function setAccesstoken($accesstoken)
    {
        $this->accesstoken = $accesstoken;
    }

    /**
     * Returns the feuserid
     *
     * @return int $feuserid
     */
    public function getFeuserid()
    {
        return $this->feuserid;
    }

    /**
     * Sets the feuserid
     *
     * @param int $feuserid
     * @return void
     */
    public function setFeuserid($feuserid)
    {
        $this->feuserid = $feuserid;
    }

    /**
     * Returns the username
     *
     * @return string $username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the username
     *
     * @param string $username
     * @return void
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Returns the userimage
     *
     * @return string $userimage
     */
    public function getUserimage()
    {
        return $this->userimage;
    }

    /**
     * Sets the userimage
     *
     * @param string $userimage
     * @return void
     */
    public function setUserimage($userimage)
    {
        $this->userimage = $userimage;
    }

    /**
     * Returns the usermail
     *
     * @return string $usermail
     */
    public function getUsermail()
    {
        return $this->usermail;
    }

    /**
     * Sets the usermail
     *
     * @param string $usermail
     * @return void
     */
    public function setUsermail($usermail)
    {
        $this->usermail = $usermail;
    }

    /**
     * Returns the captcha
     *
     * @return string $captcha
     */
    public function getCaptcha()
    {
        return $this->captcha;
    }

    /**
     * Sets the captcha
     *
     * @param string $captcha
     * @return void
     */
    public function setCaptcha($captcha)
    {
        $this->captcha = $captcha;
    }

    /**
     * Returns the paramlink
     *
     * @return string $paramlink
     */
    public function getParamlink()
    {
        return $this->paramlink;
    }

    /**
     * Sets the paramlink
     *
     * @param string $paramlink
     * @return void
     */
    public function setParamlink($paramlink)
    {
        $this->paramlink = $paramlink;
    }

    /**
     * Returns the crdate
     *
     * @return int $crdate
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * Sets the crdate
     *
     * @param int $crdate
     * @return void
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
    }

    /**
     * Returns the hidden
     *
     * @return int $hidden
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Sets the hidden
     *
     * @param int $hidden
     * @return void
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * Returns the description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $description = trim($description);

        $threeNewLines = "\r\n\r\n\r\n";
        $twoNewLines = "\r\n\r\n";
        do {
            $description = str_replace($threeNewLines, $twoNewLines, $description);
        } while (strstr($description, $threeNewLines));

        // Decode html tags
        $description = htmlspecialchars($description);
        $description = preg_replace('/(((http(s)?\:\/\/)|(www\.))([^\s]+[^\.\s]+))/', '<a href="http$4://$5$6">$1</a>', $description);

        $this->description = $description;
    }
    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->childcomment = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Adds a Comment
     *
     * @param \Nitsan\NsComments\Domain\Model\Comment $childcomment
     * @return void
     */
    public function addChildcomment(self $childcomment)
    {
        $this->childcomment->attach($childcomment);
    }

    /**
     * Removes a Comment
     *
     * @param \Nitsan\NsComments\Domain\Model\Comment $childcommentToRemove The Comment to be removed
     * @return void
     */
    public function removeChildcomment(self $childcommentToRemove)
    {
        $this->childcomment->detach($childcommentToRemove);
    }

    /**
     * Returns the childcomment
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Nitsan\NsComments\Domain\Model\Comment> $childcomment
     */
    public function getChildcomment()
    {
        return $this->childcomment;
    }

    /**
     * Sets the childcomment
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Nitsan\NsComments\Domain\Model\Comment> $childcomment
     * @return void
     */
    public function setChildcomment(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $childcomment)
    {
        $this->childcomment = $childcomment;
    }

    /**
     * @return bool
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * @param bool $terms
     * @return void
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
    }
}
