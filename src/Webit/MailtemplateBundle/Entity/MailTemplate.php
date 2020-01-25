<?php

namespace Webit\MailtemplateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Webit\MailtemplateBundle\Helper\StringConvertor as StringConvertor;

/**
 * MailTemplate
 *
 * @ORM\Table(name="webit_mail_template")
 * @ORM\Entity(repositoryClass="Webit\MailtemplateBundle\Repository\MailTemplateRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class MailTemplate
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="string", length=255, nullable=true)
     */
    private $note;

    /**
     * @var string
     *
     * @ORM\Column(name="reply_to", type="string", length=255, nullable=true)
     */
    private $reply_to;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;


    /**
    * @ORM\OneToMany(targetEntity="MailTemplateTranslation", mappedBy="translation_parent", cascade={"persist"})
    *
    */
    protected $translation;

    public function __construct()
    {
        $this->translation = new ArrayCollection();
    }

    public function __toString()
    {
        return (string)$this->getName();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return MailTemplate
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return MailTemplate
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get replyTo
     *
     * @return string
     */
    public function getReplyTo()
    {
        return $this->reply_to;
    }

    /**
     * set reply to field
     * @param type $reply_to
     * @return \Webit\MailtemplateBundle\Entity\MailTemplate
     */
    public function setReplyTo($reply_to)
    {
        $this->reply_to = $reply_to;
        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return MailTemplate
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!isset($this->createdAt)){
            $this->createdAt = new \DateTime();
        }
    }


    public function addMailTranslation(MailTemplateTranslation $translation)
    {
        $this->translation[] = $translation;
    }

    public function addTranslation(MailTemplateTranslation $translation)
    {
        $translation->setParentId($this->id);
        $this->translation[] = $translation;
    }

    /**
     * Get translation
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    public function getTranslationValues()
    {
        $trans_col = $this->getTranslation();
        $ret_arr = array();


        foreach ($trans_col as $trans) {

            $trans_obj_arr = array();
            $trans_obj_arr['lang'] = $trans->getLang();
            foreach ($trans->getTranslatableColumns() as $field) {

                $trans_obj_arr[$field] = call_user_func(array($trans, 'get' . StringConvertor::camelize($field)));
            }
            $ret_arr[$trans->getLang()] = $trans_obj_arr;
        }

        return $ret_arr;
    }

    public function setTranslationValues(array $new_translations)
    {
        $old_trans_col = $this->getTranslation();
        $old_trans_col2 = array();
        foreach ($old_trans_col as $trans) {
            $old_trans_col2[$trans->getLang()] = $trans;
        }

        foreach ($new_translations as $new_trans) {
            $lang = $new_trans['lang'];
            if (isset($old_trans_col2[$lang])) {
                $new_trans_obj = $old_trans_col2[$lang];
            } else {
                $new_trans_obj = new MailTemplateTranslation();
                $new_trans_obj->setLang($lang);
            }
            $new_trans_obj->setTranslationParent($this);

            foreach ($new_trans_obj->getTranslatableColumns() as $col) {
                call_user_func(array($new_trans_obj, 'set' . StringConvertor::camelize($col)), $new_trans[$col]);
            }
            $this->addMailTranslation($new_trans_obj);
        }
    }


    public function getSubject($lang='en')
    {
        $trans = $this->getTranslation();
        foreach($trans as $t){
            if($t->getLang()==$lang){
                return $t->getSubject();
            }
        }
        if(count($trans)>0){
            return $trans[0]->getSubject();
        }
        return 'N.A';
    }

    public function getSubjectTrans($lang='en')
    {
        $trans = $this->getTranslation();
        foreach($trans as $t){
            if($t->getLang()==$lang){
                return $t->getSubject();
            }
        }
        if(count($trans)>0){
            return $trans[0]->getSubject();
        }
        return 'N.A';
    }

    public function getMailBodyTrans($lang='en')
    {
        $trans = $this->getTranslation();
        foreach($trans as $t){
            if($t->getLang()==$lang){
                return $t->getMailBody();
            }
        }
        if(count($trans)>0){
            return $trans[0]->getMailBody();
        }
        return 'N.A';
    }


    /**
     * get email translation object by the language
     * @param string $lang
     * @return MailTemplateTranslation
     */
    public function getMailTranslation($lang = 'en')
    {
        $trans = $this->getTranslation();
        foreach($trans as $t){
            if($t->getLang()==$lang){
                return $t;
            }
        }
        if(count($trans)>0){
            return $trans[0];
        }
    }
}
