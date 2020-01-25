<?php namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Setting
 *
 * @ORM\Table(name="settings", 
 *        uniqueConstraints={
 *           @ORM\UniqueConstraint(name="key_idx", columns={"key"}) 
 *        })
 * @ORM\Entity(repositoryClass="Webit\ForexCoreBundle\Repository\SettingRepository")
 */
class Setting
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="`key`",type="string",length=255)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="`value`",type="string",length=255)
     */
    private $value;
    
    const platforms_links = array(
        'mt4_link'=>'mt4 link',
        'mt5_link'=>'mt5 link',
        'windows_link'=>'windows',
        'mac_link'=>'mac',
        'webtrader_link'=>'webtrader',
        'android_link'=>'android',
        'iphone_link'=>'iphone'
    );
    
    const homepage = array(
        'ultra_low_spreeds'=>'Ultra low Spreeds',
        'leverage_up_to'=>'Leverage Up to',
        'minimum_deposit'=>'Minimum Deposit',
        'tradable_instruments'=>'Tradable Instruments',
        'ultra_low_latency'=>'Ultra low Latency',
        'funding_methods'=>'Funding Methods'
    );
    
    const social_media_links = array(
        'twitter_link'=>'Twitter',
        'linkedin_link'=>'Linkedin',
        'facebook_link'=>'Facebook',
        'instagram_link'=>'Instagram'
    );
   
    const all = array(
        self::platforms_links,
        self::homepage,
        self::social_media_links
    );
    
    


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
     * Set key
     *
     * @param string $key
     *
     * @return Setting
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Setting
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
