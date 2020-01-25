<?php

namespace Webit\ForexCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webit\ForexCoreBundle\Entity\Ecalendar;

//Economic Calendar
class EcalendarCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('ecalendar:start')
                ->setDescription('')
        ;
    }

    /**
     * Read data from csv file and fetch it into the database
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Initialize job...");
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getEntityManager();

        $data = $this->parseEcalData();

        $output->writeln("prepare data...");
        $output->writeln("start fetching data...");
        foreach ($data as $one) {
            if (count($one) < 8) {
                continue;
            }
            $date = new \DateTime($one[0]);
            $time_tamp = $date->getTimestamp();
            $this->saveIntoDB($em, $time_tamp, $one);
            
            $output->writeln('inserted data: '.$time_tamp.' : '.$one[4]);
        }
        $output->writeln("save...");
        $output->writeln("done");
        return 0;
    }

    /**
     * saving data into database
     *
     * @param object $em
     * @param date $time_tamp
     * @param array $one
     */
    protected function saveIntoDB($em, $time_tamp, array $one)
    {
        $calendar = $this->getOrCreateCalendarObject($em, $time_tamp, $one[1], $one[4]);
        
        $calendar->setDate($time_tamp);
        $calendar->setTime($one[1]);
        $calendar->setTimeZone($one[2]);
        $calendar->setCurrency($one[3]);
        $calendar->setDescription($one[4]);
        $calendar->setImportance($one[5]);
        $calendar->setActual($one[6]);
        $calendar->setForecast($one[7]);
        $calendar->setPrevious($one[8]);
        
        $em->persist($calendar);
        $em->flush();
    }
    
    /**
     * get the ecalenar object related to the data passed or create one if it's not exist yet
     * 
     * @param Manager $em
     * @param integer $post_date
     * @param string $post_time
     * @param string $news_name
     * @return Ecalendar
     */
    protected function getOrCreateCalendarObject($em, $post_date, $post_time, $news_name){
        $calendar = $em
                ->getRepository('\Webit\ForexCoreBundle\Entity\Ecalendar')
                ->getSingleNews($post_date, $post_time, $news_name);
        
        if($calendar === null){
            $calendar = new Ecalendar();
        }
        
        return $calendar;
    }

    /**
     * Parsing data from csv file
     * @return array
     */
    protected function parseEcalData()
    {
        $last_sunday = date('m-d-Y', strtotime('last Sunday', strtotime('now')));

        if (date("l") == "Sunday") {
            $online_file_name = "Calendar-" . date('m-d-Y') . ".csv";
        } else {
            $online_file_name = "Calendar-" . $last_sunday . ".csv";
        }
       

        $csv_file = "http://www.dailyfx.com/files/$online_file_name";
        $xls_file_path = sys_get_temp_dir() . '/' . $online_file_name;
        $contextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        copy($csv_file, $xls_file_path, stream_context_create($contextOptions));
        $cvs_content = file_get_contents($xls_file_path);
        $convert = explode("\n", $cvs_content);

        $data = array();
        for ($i = 1; $i < count($convert); $i++) {
            $data[] = explode(",", $convert[$i]);
        }

        return $data;
    }

}
