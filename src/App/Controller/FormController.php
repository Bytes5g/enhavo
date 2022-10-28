<?php

namespace App\Controller;

use App\Form\Type\Form\ItemsType;
use Enhavo\Bundle\FormBundle\Form\Type\ListType;
use Enhavo\Bundle\MediaBundle\Form\Type\MediaType;
use Enhavo\Bundle\VueFormBundle\Form\VueForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/form')]
class FormController extends AbstractController
{
    private function handleForm(FormInterface $form, Request $request, $template = 'form', $theme = false)
    {
        $formView = $form->createView();
        $vueForm = $this->container->get(VueForm::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['form' => $vueForm->createData($form->createView()), 'data' => $form->getData()], $form->isValid() ? 201 : 400);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['form' => $vueForm->createData($form->createView())]);
        }

        $formView = $form->createView();

        return $this->render(sprintf('theme/form/%s.html.twig', $template), [
            'form' => $formView,
            'vue' => $vueForm->createData($formView),
            'data' => $form->getData(),
            'theme' => $theme
        ]);
    }

    #[Route('/media', name: "app_form_media")]
    public function mediaAction(Request $request)
    {
        $files = [];

        $form = $this->createForm(MediaType::class, $files, [
            'multiple' => true
        ]);

        return $this->handleForm($form, $request);
    }

    #[Route('/ajax', name: "app_form_ajax")]
    public function ajaxAction(Request $request)
    {
        $form = $this->createForm(TextType::class);
        return $this->handleForm($form, $request, 'ajax');
    }

    #[Route('/submit', name: "app_form_submit")]
    public function submitAction(Request $request)
    {
        $form = $this->createForm(TextType::class);
        return $this->handleForm($form, $request, 'submit');
    }

    #[Route('/compound', name: "app_demo_form")]
    public function compoundFormAction(Request $request)
    {
        $form = $this->createFormBuilder(null)
            ->add('date', DateType::class, [])
            ->add('datetime', TextType::class, [])
            ->add('wysiwyg', TextareaType::class, [])
            ->add('choice', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'foo' => 'foo',
                    'bar' => 'bar',
                ]
            ])
            ->add('button', SubmitType::class, [
                'label' => 'save'
            ])
            ->setMethod('POST')
            ->getForm();

        return $this->handleForm($form, $request);
    }

    #[Route('/list', name: "app_form_list")]
    public function listAction(Request $request)
    {
        $form = $this->createFormBuilder(null)
            ->add('items', ListType::class, [
                'entry_type' => ItemsType::class,
                'sortable' => true,
            ])
            ->add('button', SubmitType::class, [
                'label' => 'save'
            ])
            ->setMethod('POST')
            ->getForm();

        return $this->handleForm($form, $request);
    }

    #[Route('/list-custom', name: "app_form_list_custom")]
    public function listCustomAction(Request $request)
    {
        $form = $this->createFormBuilder(null)
            ->add('items', ListType::class, [
                'entry_type' => ItemsType::class,
                'sortable' => true,
            ])
            ->add('button', SubmitType::class, [
                'label' => 'save'
            ])
            ->setMethod('POST')
            ->getForm();

        return $this->handleForm($form, $request, theme: true);
    }

    #[Route('/diff', name: "app_form_diff")]
    public function diffAction(Request $request)
    {
        $form = $this->createFormBuilder(null)
            ->add('text', TextType::class, [
                'label' => 'My text'
            ])
//            ->add('button', SubmitType::class, [
//                'label' => 'save'
//            ])
            ->setMethod('POST')
            ->getForm();

        return $this->handleForm($form, $request, 'diff');
    }
}
