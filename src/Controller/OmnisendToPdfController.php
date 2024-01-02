<?php

namespace App\Controller;

use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\UriResolver;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OmnisendToPdfController extends AbstractController
{
    /**
     * @Route("/api/omnisend", name="omnisend", methods={"GET"})
     */
    public function saveTabsToPdf(Pdf $pdfService): Response
    {
        $urls = [
//            'https://www.vintagelakasdekor.hu/blog/5-koltseghatekony-trukk-hogy-fenyuzo-otthonod-legyen',
            'https://app.omnisend.com/REST/brandsBilling/v1/invoice/ca001c2428efcfd5b4c063554b761e45ccae29b8456d6ef7e050d718955efcbc',
        ];

        foreach ($urls as $url) {
            $title = $this->getPageTitle($url);
            $html = $this->generateHtml($url);
            $pdfContent = $pdfService->getOutputFromHtml($html);


            // Specify the public directory path
            $publicDirectory = $this->getParameter('kernel.project_dir') . '/public/';

            // Specify the subdirectory for PDFs
            $pdfSubdirectory = 'pdfs/';

            // Save PDF with the title as the filename in the public directory
            $filename = $publicDirectory . $pdfSubdirectory . $title . '.pdf';
            file_put_contents($filename, $pdfContent);

            // Output success message
            $this->addFlash('success', "Saved $url as $filename");
        }

        return new Response('Siker!');
    }

    private function getPageTitle(string $url): string
    {
        // Use Symfony's HttpClient to make a request to the URL
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $url);

        // Use Symfony's DomCrawler to parse the HTML content and extract the title
        $crawler = new Crawler($response->getContent());
        $title = $crawler->filter('title')->text();

        return $title;
    }

    private function generateHtml(string $url): string
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $url);

        // Get the base URL to use for resolving relative URLs
        $baseUrl = $this->getBaseUrl($url);

        // Use Symfony's DomCrawler to parse the HTML content
        $crawler = new Crawler($response->getContent(), $baseUrl);

        // Convert relative URLs to absolute URLs
        $uriResolver = new UriResolver();
        $crawler->filter('a, img, link')->each(function (Crawler $node) use ($uriResolver, $baseUrl) {
            $attributes = ['href', 'src', 'action'];

            foreach ($attributes as $attribute) {
                // Get the attribute value
                $value = $node->attr($attribute);

                // Skip empty or absolute URLs
                if (empty($value) || filter_var($value, FILTER_VALIDATE_URL) !== false) {
                    continue;
                }

                // Convert relative URLs to absolute URLs
                $absoluteUrl = $baseUrl . ltrim($value, '/');

                // Replace the attribute value with the absolute URL
                $node->attr($attribute);
                $element = $node->getNode(0);
                dd();
                dd($node->setAattr($attribute));
            }
        });


//        // Convert relative URLs to absolute URLs
//        $crawler->filter('a, img, link')->each(function (Crawler $node) use ($baseUrl) {
//            $attributes = ['href', 'src', 'action'];
//
//            foreach ($attributes as $attribute) {
//                // Get the attribute value
//                $value = $node->attr($attribute);
//
//                // Skip empty or absolute URLs
//                if (empty($value) || filter_var($value, FILTER_VALIDATE_URL) !== false) {
//                    continue;
//                }
//
//                // Convert relative URLs to absolute URLs
//                $absoluteUrl = $baseUrl . ltrim($value, '/');
////                dd($absoluteUrl);
//
//                // Replace the attribute value with the absolute URL
//                $node->attr($attribute, $absoluteUrl);
//            }
//        });

        // Get the updated HTML content
        $html = $crawler->html();
        dd($html);

        return $html;
    }


    private function getBaseUrl($url)
    {
        // Parse the URL
        $parsedUrl = parse_url($url);

        // Check if the URL is valid
        if ($parsedUrl !== false) {
            // Reconstruct the base URL
            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

            // Output the base URL
            return $baseUrl.'/';
        }
        return null;
    }
}
