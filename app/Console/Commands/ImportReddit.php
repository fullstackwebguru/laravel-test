<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

use App\Category;
use App\Article;

class ImportReddit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:reddit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from reddit.com';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $categoriesCount = Category::count();
        if ($categoriesCount == 0 ) {
            //if categories are not created, create them
            $this->createCategories();
        }

        $categories = Category::all();
        
        //remove all aritcles before fetching reddit
        Article::truncate();

        //fetch reddit pages and store articles
        $totalCount = 0;
        $client =new Client(['base_uri' => 'https://www.reddit.com']);

        foreach ($categories as $category) {
            $totalCount += $this->fetchRedditPage($client, $category->url, $category->id);
        }

        echo "Total {$totalCount} number of Articles are imported";
    }

    /**
     * Fetch reddit pages and store articles
     *
     * @return void
     */
    private function createCategories()
    {
        $categories = [
            ['name' => 'hot', 'url' => '/'],
            ['name' => 'new', 'url' => '/top/'],
            ['name' => 'rising', 'url' => '/rising/'],
            ['name' => 'controversial', 'url' => '/controversial/'],
            ['name' => 'gilded', 'url' => '/gilded/'],
        ];

        Category::insert($categories);
    }

    /**
     * Fetch reddit pages and store articles
     *
     * @return number
     */
    private function fetchRedditPage($client, $url, $categoryId)
    {
        echo "importing {$url} \r\n";
        $res = $client->request('GET', $url, [
            'headers' => [
                'User-Agent' =>  'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.117 Safari/537.36'
            ]
        ]);
        
        $statusCode = $res->getStatusCode();
        if ($statusCode == 200) {
            $html = (string)$res->getBody();
            $crawler = new Crawler($html);

            //GET Titles with CSS selector
            $titles = $crawler->filter('#siteTable .entry p.title')->each(function (Crawler $node, $i) {
                return $node->text();
            });

            //Create Article with title
            foreach($titles as $title) {
                echo $title . "\r\n";
                $article = new Article;
                $article->title = $title;
                $article->category_id = $categoryId;
                $article->save();
            }

            $count = count($titles);
            echo "{$count} articles \r\n";
            return $count;
        }

        echo "Error Occured : status code - {$statusCode}";
        return 0;
    }
}
