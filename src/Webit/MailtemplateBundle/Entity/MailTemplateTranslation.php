<?php

namespace Webit\MailtemplateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webit\MailtemplateBundle\Entity\MailTemplate as MailTemplate;

/**
 * MailTemplateTranslation
 *
 * @ORM\Table(name="webit_mail_template_translation", indexes={@ORM\Index(name="mail_template_translation_fk1", columns={"parent_id"})})
 * @ORM\Entity
 */
class MailTemplateTranslation
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
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="mail_body", type="text", nullable=true)
     */
    private $mailBody;

    /**
     * @var \MailTemplate
     *
     * @ORM\ManyToOne(targetEntity="MailTemplate", inversedBy="translation", cascade={"remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $translation_parent;


    /**
     * @var String
     *
     * @ORM\Column(name="lang", type="text", nullable=false)
     */
    private $lang;

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
     * Set subject
     *
     * @param string $subject
     * @return MailTemplateTranslation
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set lang
     *
     * @param string $subject
     * @return MailTemplateTranslation
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set mailBody
     *
     * @param string $mailBody
     * @return MailTemplateTranslation
     */
    public function setMailBody($mailBody)
    {
        $this->mailBody = $mailBody;

        return $this;
    }

    /**
     * Get mailBody
     *
     * @return string
     */
    public function getMailBody()
    {
        return $this->mailBody;
    }

    /**
     * Set Translation Parent
     *
     * @param MailTemplate $translation_parent
     * @return MailTemplateTranslation
     */
    public function setTranslationParent(MailTemplate $translation_parent = null)
    {
        $this->translation_parent = $translation_parent;

        return $this;
    }

    /**
     * Get Translation Parent
     *
     * @return MailTemplate
     */
    public function getTranslationParent()
    {
        return $this->translation_parent;
    }

      public static function getTranslatableColumns()
      {
          return array('subject','mail_body');
      }
}
