<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Storefront\Controller;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Cart\ProductCartProcessor;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class WeRepackController extends StorefrontController
{
    private LineItemFactoryRegistry $lineItemFactory;
    private CartService $cartService;

    public function __construct(LineItemFactoryRegistry $lineItemFactory, CartService $cartService)
    {
        $this->lineItemFactory = $lineItemFactory;
        $this->cartService = $cartService;
    }

    /**
     * @Route("/checkout/we-repack/add", name="frontend.checkout.mrpix_we_repack.add", defaults={"XmlHttpRequest": true}, methods={"POST"})
     */
    public function addWeRepack(Request $request, Cart $cart, SalesChannelContext $context): JsonResponse
    {
        $context->setPermissions([ProductCartProcessor::ALLOW_PRODUCT_PRICE_OVERWRITES => true]);
        // TODO: somehow this is not working!? => method broken ?!?!?!
        // TODO: check if some validation etc. is failing
        $lineItem = $this->lineItemFactory->create([
            'type' => LineItem::CUSTOM_LINE_ITEM_TYPE,
            'label' => 'We Repack',
            'referencedId' => Uuid::randomHex(),
            'quantity' => 1,
            'payload' => ['we-repack' => true]
        ], $context);
        $this->cartService->add($cart, $lineItem, $context);

        return new JsonResponse(['success' => true]);
    }
}