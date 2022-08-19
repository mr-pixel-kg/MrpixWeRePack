<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Storefront\Controller;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/checkout/we-repack/add", name="frontend.checkout.mrpix_we_repack.add", methods={"POST"})
     */
    public function addWeRepack(Request $request, Cart $cart, SalesChannelContext $context): Response
    {
        // TODO: implement some logic to save if order is repack and there is no promotion on the cart
        return $this->redirectToRoute('frontend.checkout.cart.page');
    }
}