<?php declare(strict_types=1);

namespace Elio\ElioFactFinder\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Defaults;

/**
 * @RouteScope(scopes={"storefront"})
 */
class CSVController extends AbstractController
{
    /**
     * @var EntityRepositoryInterface
     */
    private $repository;
    /**
     * @var ProductEntity[]
     */
    private $products;


    /**
     * @Route("/generate/csv", name="generate.csv", methods={"GET"})
     */
    public function generateProductExportCSVFile(Request $request, Context $context): JsonResponse
    {

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="factfinder.csv"');

        $this->repository = $this->container->get('product.repository');
        $this->products = $this->repository->search(new Criteria(), $context)->getElements();

        $headers = [
            'ProductID',
            'Name',
            'Description',
            //'ProductURL',
            //'Price',
            'Manufacturer',
            'EAN',
            'Keywords'

        ];

        $file = fopen('php://output', 'wb');
        $data = [];

        // save the column headers
        fputcsv($file, $headers, ';');

        foreach ($this->products as $product){
            $manufacturerName = "";

            if (!empty($product->getManufacturer())){
                $manufacturerName = $product->getManufacturer()->getName();
            }
            array_push($data, array(
                $product->getId(),
                $product->getName(),
                $product->getDescription(),
                //$product->getSeoUrls(),
                //$product->getPrice(),
                $manufacturerName,
                $product->getEan(),
                $product->getKeywords(),
            ));
        }

        // save each row of the data
        foreach ($data as $row)
        {
            fputcsv($file, $row, ';');
        }

        // Close the file
        fclose($file);

        return new JsonResponse("Product export csv file successful generated");
    }

}
