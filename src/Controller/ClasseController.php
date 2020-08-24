<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Entity\Course;
use App\Entity\Student;
use App\Repository\ClasseRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ClasseController
 * @package App\Controller
 * @Route("/classe", name="classe.")
 */
class ClasseController extends Controller
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
//        $classes = $this->getDoctrine()->getRepository(Classe::class)->findAll();

        $page = $request->query->get('page');
        if($page == null)
            $page = 1;
        $pages['currentPage'] = (int) $page;
        $pages['limit'] = 10;
        $classes = $this->getDoctrine()->getRepository(Classe::class)->paginate($pages['currentPage'],$pages['limit']);
        $totalStudents = count($this->getDoctrine()->getRepository(Classe::class)->findAll());

        $pages['totalPages'] = ceil($totalStudents/$pages['limit']);


        return $this->render('classe/index.html.twig', [
            'classes' => $classes,
            'pages' => $pages,

        ]);
    }

    /**
     * @Route("/details/{id}", name="details")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function details($id, Request $request){
        $classe = $this->getDoctrine()->getRepository(Classe::class)->find($id);
        $students = $classe->getStudent();
//        $totalGrade = array();
//        foreach ($students as $student){
//            $averageGrade = 0.0;
//            if(sizeof($student->getGrades()) != 0){
//
//                foreach ($student->getGrades() as $grade){
//                    $averageGrade += $grade->getGrade();
//                }
//                $averageGrade /= sizeof($student->getGrades());
//            }
//            array_push($totalGrade,$averageGrade);
//        }

        $filterForm = $this->createFormBuilder()
            ->setMethod('GET')
            ->add('grade', TextType::class)
            ->add('filter', SubmitType::class)
            ->getForm();

        $filterForm->handleRequest($request);

        if($filterForm->isSubmitted() && $filterForm->isValid()){
            $filteredGrade = $filterForm->getData();
            $students = $this->getDoctrine()->getRepository(Student::class)->findBy(['gpa' => $filteredGrade]);


        }


        return $this->render('classe/details.html.twig',[
            'classe'=> $classe,
            'students' => $students,
//            'grades' => $totalGrade,
            'filterForm' => $filterForm->createView(),
        ]);
    }

    /**
     * @Route("/create", name = "create")
     * @param Request $request
     * @return Response
     */
    public function createClass(Request $request){
        $classe = new Classe();


        $form = $this->createFormBuilder($classe)
            ->add('name',TextType::class)
//            ->add('student', EntityType::class, [
//                'class' => Student::class,
//                'choice_value' => 'id',
//                'choice_label' => function(?Student $student){
//                    return $student ? $student->getFirstName() . ' ' . $student->getLastName() : '';
//                },
//                'multiple' => true,
//                'expanded' => true,
//                'label' => 'Students'
//            ])
//            ->add('courses', EntityType::class, [
//                'class' => Course::class,
//                'choice_value' => 'id',
//                'choice_label' => function(?Course $course) {
//                    return $course ? $course->getName() : '';
//                },
//                'multiple' => true,
//                'expanded' => true,
//                'label' => 'Courses'
//            ])
            ->add('save', SubmitType::class, ['label' => 'Create'])
            ->getForm();


        $form->handleRequest($request);
        $courses = $classe->getCourses();


        if($form->isSubmitted() && $form->isValid()){
            $classe = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($classe);

//            foreach ($classe->getStudent() as $i){
//                $student = $em->getRepository(Student::class)->find($i->getId());
//                $student->setClasse($classe);
//            }

//            foreach ($courses as $course){
//                $course->getClasse()->add($classe);
//            }

            $em->flush();

            $this->addFlash(
                'success',
                'Class was created'
            );
            return $this->redirectToRoute('classe.index');
        }
        return $this->render('classe/classe_form.html.twig',[
            'form' => $form->createView(),
            'title' => 'Create',
        ]);
    }

    /**
     * @Route("/edit/{id}", name = "edit")
     * @param Classe $classe
     * @param Request $request
     * @return Response
     */
    public function editClasse(Classe $classe, Request $request){
        $form = $this->createFormBuilder($classe)
            ->add('name',TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Edit'])
            ->getForm();


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash(
                'success',
                'Class was edited'
            );
            return $this->redirectToRoute('classe.index');
        }
        return $this->render('classe/classe_form.html.twig',[
            'form' => $form->createView(),
            'title' => 'Edit'
        ]);

    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @param Classe $classe
     * @return RedirectResponse
     */
    public function delete(Classe $classe){

        $students = $classe->getStudent();
        foreach ($students as $student){
            $student->setClasse(null);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($classe);
        $em->flush();

        $this->addFlash(
            'success',
            'Class was removed'
        );

        return $this->redirectToRoute('classe.index');
    }
}
