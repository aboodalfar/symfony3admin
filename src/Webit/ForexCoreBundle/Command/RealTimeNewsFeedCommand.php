<?php

namespace Webit\ForexCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webit\ForexCoreBundle\Entity\RealTimeNews;
use Zrashwani\NewsScrapper;

class RealTimeNewsFeedCommand extends ContainerAwareCommand
{
    private $feeds;
    private $contents = array();
    private $links = array();

    protected function configure()
    {
        parent::configure();

        $this->setName('forex:rssnews:feed')
                ->addArgument('index', InputArgument::OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->feeds = array(

            //'http://www.icn.com' => array('http://www.icn.com/rss/en/news.xml', '#content'),
            'https://www.dailyfx.com'=>array('https://www.dailyfx.com/feeds/forex_market_news','//div[@class="story_body"]'),
            //'http://www.ibtimes.com'=>array('http://feeds.feedburner.com/IbtimescomEconomy?format=xml','[itemprop="articleBody"]'),
//            'http://www.fxstreet.com' => array('http://xml.fxstreet.com/news/forex-news/index.xml', '//div[@class="article-text"]'),
            //'http://money.cnn.com' => array('http://rss.cnn.com/rss/money_mostpopular.rss', '#storytext'),
            //'http://gafnn.com' => array('http://gafnn.com/gafnn-blogs/latest?format=feed&type=rss', '.blog-text'),
        );
        $index = $input->getArgument('index');
        if (isset($index)) {
            $this->setFeed($index);
        }


        $output->writeln("Start ...");

        $this->start();

        $output->writeln("done");
        return 0;
    }

    public function setFeed($id)
    {
        if ($id >= 0 && $id < count($this->feeds)) {
            $i = 0;
            foreach ($this->feeds as $dom => $feed) {
                if ($i === $id) {
                    $this->feeds = array(
                        $dom => $feed
                    );
                    break;
                }
                $i++;
            }
        } else
            echo "out of range offset " . $id . "\r\n";
    }

    public function start()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();


        foreach ($this->feeds as $domain => $feed) {
            echo "|- start getting feeds from <" . $domain . ">\r\n";

            $this->RSSReaders($domain);

     
            foreach ($this->contents as $html => $content) {
                $news = $doctrine->getRepository('WebitForexCoreBundle:RealTimeNews')->findOneBy(array('link' => $html));

                if (!is_object($news) or $news == null) {
                    echo "\t |- start parsing  HTML \r\n";

                 //    $this->HTMLReaders($html, $domain);

                    // print_r($this->contents[$html]);
                    $news = new RealTimeNews();
                    $title = $this->contents[$html]['title'];
                    $body = $this->contents[$html]['content'];
                    $title = strtr($title, array('“' => '"', '”' => '"'));
                    $body = strtr($body, array('“' => '"', '”' => '"'));
                    //echo 'Trying to insert: '.$html.': '.chr(10).$title.chr(10);
                    $news->setLink($html);
                    $news->setLang('en');
                    $news->setContent($body);
                    $news->setCreatedAt($this->contents[$html]['created_at']);
                    $news->setTitle($title);

                    $em->persist($news);
                    $em->flush();

                    echo "\t\t |- insarting to database .....\r\n";
                }
            }
        }
        echo "\r\n\t\t\t|-Completed-|\r\n";
    }

    public function RSSReaders($domain)
    { 
        
        $ch = curl_init($this->feeds[$domain][0]);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML($response);
        $items = $xmlDoc->getElementsByTagName('item');
        foreach ($items as $item) {
            $link = $item->getElementsByTagName('link')->item(0)->nodeValue;

            $title = $item->getElementsByTagName('title')->item(0)->nodeValue;
            $created_at = $item->getElementsByTagName('pubDate')->item(0)->nodeValue;
            $content = $item->getElementsByTagName('description')->item(0)->nodeValue;

            $this->contents[$link] = array('content'=>$content,'title' => $title, 'created_at' => new \DateTime($created_at));
        }
    }

    public function HTMLReaders($link, $domain)
    {
      
        $selector = $this->feeds[$domain][1];
        
        $scrapClient = new NewsScrapper\Client('Custom');                
        $adapter = $scrapClient->getAdapter();        
        $adapter->setBodySelector($selector);
         
        $linkData = $scrapClient->getLinkData($link);
        
        $this->contents[$link]['content'] = $linkData->body;
    }

}