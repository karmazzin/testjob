<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Product;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render(
          'AppBundle:Default:index.html.twig'
        );
    }

    /**
     * @Route(
     *  "/api/products/{id}",
     *  requirements={"id": "\d+"},
     *  defaults={"id": 0}
     * )
     * @Method({"DELETE"})
     */
    public function deleteProduct(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect('/');
        }

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Product');
        $product = $repository->find($request->get('id'));

        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }

        $em->remove($product);
        $em->flush();

        return new Response(null, 200);

    }

    /**
     * @Route(
     *  "/api/products/{id}",
     *  requirements={"id": "\d+"},
     *  defaults={"id": 0}
     * )
     * @Method({"GET"})
     */
    public function getProductsAction(Request $request) {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect('/');
        }

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Product');

        if ($id = $request->get("id")) {
            $result = $repository->findById($id);
        } else {
            $result = $repository->findAll();
        }

        if (!$result) {
            throw $this->createNotFoundException('The product does not exist');
        }

        $serializer = $this->get('jms_serializer');

        return new Response($serializer->serialize($result, 'json'));
    }


    /**
     * @Route("/api/products")
     * @Method({"POST"})
     */
    public function addProductAction(Request $request)
    {

        if (!$request->isXmlHttpRequest()) {
            return $this->redirect('/');
        }


        $em = $this->getDoctrine()->getManager();

        $product = new Product();

        $product
          ->setTitle($request->request->get('title'))
          ->setDescription($request->request->get('description', ''))
          ->setPhoto($request->files->get('image'));

        $validate_group = ['upload'];
        $result = $this->validatePersistProduct($product, $validate_group);

        return $result;
    }

    /**
     * @Route(
     *  "/api/products/{id}",
     *  requirements={"id": "\d+"},
     *  defaults={"id": 0}
     * )
     * @Method({"POST"})
     */
    public function updateProductAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect('/');
        }


        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('AppBundle:Product');
        $product = $repository->find($request->get('id'));

        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }

        $product
          ->setTitle($request->request->get('title'))
          ->setDescription($request->request->get('description', ''));

        if ($photo = $request->files->get('image')) {
            $product->removePhoto();
            $product->setPhoto($request->files->get('image'));
            $validate_group = ['upload'];
        } else {
            $validate_group = [];
        }

        $result = $this->validatePersistProduct($product, $validate_group);

        return $result;

    }


    protected function validatePersistProduct($product, $validate_group) {

        $serializer = $this->get('jms_serializer');
        $validator = $this->get('validator', $validate_group);
        $errors = $validator->validate($product);

        if (count($errors) > 0) {
            return new Response($serializer->serialize($errors, 'json'), 400);
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return new Response($serializer->serialize($product, 'json'), 200);
        }
    }
}
