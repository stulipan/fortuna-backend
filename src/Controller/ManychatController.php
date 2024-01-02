<?php

namespace App\Controller;

use App\Entity\ApiError;
use App\Entity\Enums;
use App\Entity\HoroscopeTextPublished;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ManychatController extends StulipanBaseController
{
    /**
     * @Route("/api/manychat/populate/{dateString}", name="manychat-populateBotFields", methods={"POST"})
     */
    public function populateBotFieldsArray(Request $request, EntityManagerInterface $em, string $dateString = null)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new BadRequestHttpException('Invalid JSON');
        }
        $prefix = isset($data['prefix']) ? $data['prefix'] : 'Szia, kedves';
        $prefixAfterName = isset($data['prefixAfterName']) ? $data['prefixAfterName'] : '!';
        $postfix = isset($data['postfix']) ? $data['postfix'] : '(Kérlek nyomj a gombra vagy írj ide valamit, hogy holnap is el tudjam küldeni a horoszkópod!)';
        $midfix = isset($data['midfix']) ? $data['midfix'] : '(A folytatáshoz nyomj a Tovább gombra!)';

        if (!$dateString) {
          $dateString = date(Enums::DATE_FORMAT); // same with: (new \DateTime())->format('Y-m-d')
        }
        $horoscopeDate = $dateString;

        $baseFields = $this->initBotFieldsArray('base');
        $addendumFields = $this->initBotFieldsArray('addendum');
        $prefixFields = $this->initBotFieldsArray();

//        $matchingField = array_filter($prefixFields, function ($field) use ($prefix) {
//            return strpos($field['field_name'], '_prefix' ) !== false;
//        });
//        dump($matchingField);
//        if (!empty($matchingField)) {
//            $matchingFieldKey = key($matchingField);
//            $prefixFields[$matchingFieldKey]['field_value'] = $prefix;
//        }

        $prefixFields[0]['field_value'] = $prefix;
        $prefixFields[1]['field_value'] = $prefixAfterName;
        $prefixFields[2]['field_value'] = $postfix;
        $prefixFields[3]['field_value'] = $midfix;
        $prefixFields[4]['field_value'] = $horoscopeDate;

        $publishDate = $this->createDateFromFormat($dateString);
        if ($publishDate === null) {
            return $this->jsonErrorResponse([
                'error' => ApiError::UNPROCESSABLE_ENTITY,
                'message' => 'Hiányzik a dateString vagy érvénytelen.',
            ], 422);
        }

        $publishedItems = $em
            ->getRepository(HoroscopeTextPublished::class)
            ->findBy(['publishDate' => $publishDate])
        ;

        if (empty($publishedItems)) {
            return $this->jsonErrorResponse([
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => sprintf('Nem talált publikálásokat erre a dátumra (%s)!', $dateString),
            ], 404);
        }

        if (count($publishedItems) != 12) {
            return $this->jsonErrorResponse([
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => sprintf('12-nél kevesebb publikálást talált erre a dátumra (%s)!', $dateString),
            ], 404);
        }

        // Match zodiac signs and update $fields array
        foreach ($publishedItems as $publishedItem) {
            $zodiacSign = $publishedItem->getAstrologicalSign()->getName();
            $baseText = $publishedItem->getHoroscopeText()->getBase();
            $addendumText = $publishedItem->getHoroscopeText()->getAddendum();

            // Find the corresponding zodiac sign in the $fields array
            $matchingField = array_filter($baseFields, function ($field) use ($zodiacSign) {
                return strpos($field['field_name'], '_' . $zodiacSign . '_Base') !== false;
            });
            if (!empty($matchingField)) {
                $matchingFieldKey = key($matchingField);
                $baseFields[$matchingFieldKey]['field_value'] = $baseText;
            }

            $matchingField = array_filter($addendumFields, function ($field) use ($zodiacSign) {
                return strpos($field['field_name'], '_' . $zodiacSign . '_Addendum') !== false;
            });
            if (!empty($matchingField)) {
                $matchingFieldKey = key($matchingField);
                $addendumFields[$matchingFieldKey]['field_value'] = $addendumText;
            }
        }

        $mergedFields = array_merge($baseFields, $addendumFields, $prefixFields);
        // filter our items that don't have 'field_value' fields
        $fields = array_filter($mergedFields, function ($item) {
            return isset($item['field_value']);
        });

//        dd($fields);
        $transformedArray = ['fields' => []];

        foreach ($fields as $item) {
            // Check if the item has both "field_name" and "field_value"
            if (isset($item['field_name']) && isset($item['field_value'])) {
                // Create a new associative array for each item
                $transformedArray['fields'][] = [
                    'field_name' => $item['field_name'],
                    'field_value' => $item['field_value'],
                ];
            }
        }

//        dd($transformedArray);

        $apiKey = $_ENV['MANYCHAT_TOKEN'];
        $manychatHost = 'https://api.manychat.com/';
        $manychatUrl = $manychatHost.'fb/page/setBotFields';
        $client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);

        if (count($transformedArray['fields']) > 20) {
            $firstBatch['fields'] = array_slice($transformedArray['fields'], 0, 20);
            $secondBatch['fields'] = array_slice($transformedArray['fields'], 20);
        } else {
            $firstBatch['fields'] = $transformedArray['fields'];
        }

//        dd(json_encode($firstBatch));

        $response = $client->post($manychatUrl, [
            'json' => $firstBatch,
        ]);

        $result = json_decode($response->getBody(), true);

//        dd($result);

        if ($result['status'] === 'error') {
            $errorMessage = isset($result['message']) ? $result['message'] : 'Unknown error';

            return new JsonResponse([
                'error' => 'Manychat API error',
                'message' => $errorMessage,
                'details' => isset($result['details']) ? $result['details'] : null,
            ], 500, []);
        }

        if (isset($secondBatch)) {
            $response = $client->post($manychatUrl, [
                'json' => $secondBatch,
            ]);

            $result = json_decode($response->getBody(), true);

            if ($result['status'] === 'error') {
                $errorMessage = isset($result['message']) ? $result['message'] : 'Unknown error';

                return new JsonResponse([
                    'error' => 'Manychat API error',
                    'message' => $errorMessage,
                    'details' => isset($result['details']) ? $result['details'] : null,
                ], 500, []);
            }
        }

        return new JsonResponse($result, 200, []);
    }

    private function initBotFieldsArray(string $type = null)
    {
        $jsonFilePath = $this->getParameter('kernel.project_dir') . '/src/Resources/BotFields.json';

        if (!file_exists($jsonFilePath)) {
            return new Response('JSON file not found!', Response::HTTP_NOT_FOUND);
        }

        $jsonContent = file_get_contents($jsonFilePath);     // Read the JSON content
        $data = json_decode($jsonContent, true);

//        foreach ($data['data'] as &$item) {
//            // Check if the 'name' key matches the specified criteria
//            if (preg_match('/^b_horoscopeText_(\d+)_(\w+)$/', $item['name'], $matches)) {
//                // Extract numeric and text parts from the current 'name' value
//                $numericPart = $matches[1];
//                $zodiacSign = $matches[2];
//
//                // Construct the new 'name' value with the updated prefix, numeric part, and zodiac sign
//                $newName = 'b_' . str_pad($numericPart, 2, '0', STR_PAD_LEFT) . '_' . $zodiacSign . '_Base';
//
//                // Update the 'name' key in the current item
//                $item['name'] = $newName;
//            }
//        }
//        dd($data['data']);
        $searchString = null;
        if ($type) {
            if ($type == 'base') {
                $searchString = '_Base';
            }
            if ($type == 'addendum') {
                $searchString = '_Addendum';
            }
        }

        if ($searchString) {
            $filteredData = [];
            foreach ($data['data'] as $item) {
                // Check if the 'name' key ends with "_Base"
                if (substr($item['name'], -strlen($searchString)) === $searchString) {
                    $filteredData[] = $item;
                }
            }
//            dd($filteredData);

            $fields = array_map(function ($item) {
                return ['field_name' => $item['name']];
            }, $filteredData);

            return $fields;
        }

        $fields[] = ['field_name' => 'b_prefix'];
        $fields[] = ['field_name' => 'b_prefixAfterName'];
        $fields[] = ['field_name' => 'b_postfix'];
        $fields[] = ['field_name' => 'b_midfix'];
        $fields[] = ['field_name' => 'b_horoscopeDate'];

        return $fields;
    }

}
