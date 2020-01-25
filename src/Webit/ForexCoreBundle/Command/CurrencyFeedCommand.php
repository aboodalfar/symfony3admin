<?php

namespace Webit\ForexCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webit\ForexCoreBundle\Entity\Currency;

class CurrencyFeedCommand extends ContainerAwareCommand
{
    private  $T_HOST ;  // MetaTrader Server Address //TODO: replace with Actual Server
      private $T_PORT  ;                   // MetaTrader Server Port
     private $T_TIMEOUT ;               // MetaTrader Server Connection Timeout, in sec
     private $T_QUOTES ;
    const T_CACHEDIR = '../qoutes/';         // cache files directory
    const T_CACHETIME = 5;               // cache expiration time, in sec
    const T_CLEAR_DELNUMBER = 15;        // limit of deleted files, after which process of cache clearing should be stopped

    public static $MQ_CLEAR_STARTTIME = 0; // time
    public static $MQ_CLEAR_NUMBER = 0;    // deleted files counter

    protected function configure()
    {
        parent::configure();

        $this->setName('currency:feed')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $currency_feed = $this->getContainer()->getParameter('currency_feed');
        $this->T_HOST=$currency_feed['t_host'];
        $this->T_PORT=$currency_feed['t_port'];
        $this->T_TIMEOUT=$currency_feed['t_timeout'];
        $this->T_QUOTES=$currency_feed['t_quotes'];
        $output->writeln("Start ...");

	$output->writeln("connecting to ".$this->T_HOST.' port:'.$this->T_PORT);
        $this->getFeed();
        $output->writeln("done");
        return 0;
    }

    public function getFeed()
    {
        $query = "QUOTES-" . $this->T_QUOTES;
        $query_result = $this->cacheQuery($query);
        echo ($query_result);        
        if ($query_result != '!!!CAN\'T CONNECT!!!') {
            
            $lines = explode("\n", $query_result);
            foreach ($lines as $line) {
                if (isset($line[0]) && ($line[0] == 'u' || $line[0] == 'd')) {

                    $tmp = explode(' ', $line);
                    //------------------- SHOW ONE ROW --------------------------------
                    //$tmp => [0]Status, [1]Symbol, [2]Bid, [3]Ask, [4]Date, [5]Time, [6]High, [7]Low
                    $this->currencyFeedUpdate($tmp);
                }
            }
        }
    }

    public function cacheQuery($query, $cacheDir = self::T_CACHEDIR, $cacheTime = self::T_CACHETIME, $cacheDirPrefix = '')
    {
        $result = '';
        $file_name = $this->getContainer()->getParameter('kernel.root_dir') . '/' . $cacheDir . $cacheDirPrefix . crc32($query); // cache file name
      //  var_dump($file_name);
        if (file_exists($file_name) && (time() - filemtime($file_name)) < $cacheTime) {
            $result = file_get_contents($file_name);
          
        } else {

            $sock = @fsockopen($this->T_HOST, $this->T_PORT, $errno, $errstr, $this->T_TIMEOUT);
            if ($sock) {
                //--- If having connected, request and collect the result
                if (fputs($sock, "W$query\nQUIT\n") != FALSE)
                    while (!feof($sock)) {
                        if (($line = fgets($sock, 128)) == "end\r\n")
                            break;
                        $result .= $line;
                    }
                fclose($sock);
                if ($cacheTime > 0) {
                    //--- If there is a prefix (login, for example), create a nonpresent directory for storing the cache
                    if ($cacheDirPrefix != '' && !file_exists($this->getContainer()->getParameter('kernel.root_dir') . '/' . $cacheDir . $cacheDirPrefix)) {
                        foreach (explode('/', $cacheDirPrefix) as $tmp) {
                            if ($tmp == '' || $tmp[0] == '.')
                                continue;
                            $cacheDir .= $tmp . '/';
                            if (!file_exists($this->getContainer()->getParameter('kernel.root_dir') . '/' . $cacheDir))
                                @mkdir($this->getContainer()->getParameter('kernel.root_dir') . '/' . $cacheDir);
                        }
                    }
                    //--- save result into cache
                    $fp = @fopen($file_name, 'w');
                    if ($fp) {
                        fputs($fp, $result);
                        fclose($fp);
                    }
                }
            } else {
                //--- if connection fails, show the old cache (if there is one) or return with the error
                if (file_exists($file_name)) {
                    touch($file_name);
                    $result = file_get_contents($file_name);
                } else {
                    $result = '!!!CAN\'T CONNECT!!!';
                }
            }
        }
        //--- clear cache every 3 sec (for such frequency of calls)
        if (!file_exists($this->getContainer()->getParameter('kernel.root_dir') . '/' . self::T_CACHEDIR . '.clearCache') || (time() - filemtime($this->getContainer()->getParameter('kernel.root_dir') . '/' . self::T_CACHEDIR . '.clearCache')) >= 3) {
            ignore_user_abort(true);
            @mkdir($this->getContainer()->getParameter('kernel.root_dir') . '/' . self::T_CACHEDIR . '.clearCache', 0777, true);
            touch($this->getContainer()->getParameter('kernel.root_dir') . '/' . self::T_CACHEDIR . '.clearCache');


            self::$MQ_CLEAR_STARTTIME = time();

            $this->clearCache(realpath($this->getContainer()->getParameter('kernel.root_dir') . '/' . self::T_CACHEDIR));

            ignore_user_abort(false);
        }

        return $result;
    }

    public function clearCache($dirName)
    {
        if (empty($dirName) || ($list = glob($dirName . '/*')) === false || empty($list))
            return;
        //---

        $size = sizeof($list);
        foreach ($list as $fileName) {
            $baseName = basename($fileName);
            if ($baseName[0] == '.')
                continue;
            if (is_dir($fileName)) {
                //--- go through all cache directories recursively
                $this->clearCache($fileName);
                if (self::$MQ_CLEAR_NUMBER >= self::T_CLEAR_DELNUMBER)
                    return; // by recursion check condition for function exit
            } elseif ((self::$MQ_CLEAR_STARTTIME - filemtime($fileName)) > self::T_CACHETIME) {
                //--- if the file time is expired, delete it and, if the limit of deleted files has been exceeded, exit
                @unlink($fileName);
                if (++self::$MQ_CLEAR_NUMBER >= self::T_CLEAR_DELNUMBER)
                    return;
                --$size;
            }
        }
        //--- delete empty directory
        $tmp = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/' . self::T_CACHEDIR);
        if (!empty($tmp) && $size <= 0 && strlen($dirName) > strlen($tmp) && $dirName != $tmp)
            @rmdir($dirName);
    }

    public function currencyFeedUpdate($data)
    {
        //$tmp => [0]Status, [1]Symbol, [2]Bid, [3]Ask, [4]Date, [5]Time, [6]High, [7]Low
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getEntityManager();
        $currency = $doctrine->getRepository('WebitForexCoreBundle:Currency')->findOneBy(array('name' => $data[1]));
        if (!(is_object($currency) and $currency instanceof Currency)) {
            $currency = new Currency();
            $currency->setName($data[1]);
        }
        $currency->setPrice($data[2]);
        $currency->setStatus($data[0]);
        $currency->setAsk($data[3]);
        if (isset($data[6])) {
            $currency->setHigh($data[6]);
        }
        if (isset($data[7])) {
            $currency->setLow($data[7]);
        }
        $em->persist($currency);
        $em->flush();
    }

    public function RSSReaders($domain)
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->load($this->feeds[$domain][0]);
        $items = $xmlDoc->getElementsByTagName('item');
        foreach ($items as $item) {

            $link = $item->getElementsByTagName('link')->item(0)->nodeValue;
            $title = $item->getElementsByTagName('title')->item(0)->nodeValue;
            $created_at = $item->getElementsByTagName('pubDate')->item(0)->nodeValue;
            $content = $item->getElementsByTagName('description')->item(0)->nodeValue;
            $this->contents[$link] = array('title' => $title, 'created_at' => new \DateTime($created_at));
        }
    }

}
