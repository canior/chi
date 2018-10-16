<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $form_full_class_name ?>;
<?php if (isset($repository_full_class_name)): ?>
use <?= $repository_full_class_name ?>;
<?php endif ?>
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class <?= $class_name ?> extends BackendController
{
    /**
     * @Route("<?= $route_path ?>/", name="<?= $route_name ?>_index", methods="GET")
     */
<?php if (isset($repository_full_class_name)): ?>
    public function index(<?= $repository_class_name ?> $<?= $repository_var ?>, Request $request): Response
    {
        $data = [
            'title' => '<?= $entity_class_name ?> 列表',
            'form' => [
                'keyword' => $request->query->get('keyword', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = $<?= $repository_var ?>->findByKeyword($data['form']['keyword']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/<?= $route_name ?>/index.html.twig', $data);
    }
<?php else: ?>
    public function index(): Response
    {
        $<?= $entity_var_plural ?> = $this->getDoctrine()
            ->getRepository(<?= $entity_class_name ?>::class)
            ->findAll();

        return $this->render('backend/<?= $route_name ?>/index.html.twig', ['<?= $entity_twig_var_plural ?>' => $<?= $entity_var_plural ?>]);
    }
<?php endif ?>

    /**
     * @Route("<?= $route_path ?>/new", name="<?= $route_name ?>_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $<?= $entity_var_singular ?> = new <?= $entity_class_name ?>();
        $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($<?= $entity_var_singular ?>);
            $em->flush();
            $this->addFlash('notice', '添加成功');
            return $this->redirectToRoute('<?= $route_name ?>_index');
        }

        return $this->render('backend/<?= $route_name ?>/new.html.twig', [
            '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
            'title' => '添加 <?= $entity_class_name ?>',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("<?= $route_path ?>/{<?= $entity_identifier ?>}/edit", name="<?= $route_name ?>_edit", methods="GET|POST")
     */
    public function edit(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
        $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('<?= $route_name ?>_edit', ['<?= $entity_identifier ?>' => $<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>()]);
        }

        return $this->render('backend/<?= $route_name ?>/edit.html.twig', [
            '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
            'title' => '修改 <?= $entity_class_name ?>',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("<?= $route_path ?>/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_delete", methods="DELETE")
     */
    public function delete(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
        if ($this->isCsrfTokenValid('delete'.$<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($<?= $entity_var_singular ?>);
            $em->flush();
            $this->addFlash('notice', '删除成功');
        }

        return $this->redirectToRoute('<?= $route_name ?>_index');
    }
}
