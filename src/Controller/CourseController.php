<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\Student;
use App\Repository\CourseRepository;
use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CourseController
 * @package App\Controller
 * @Route("/course", name="course.")
 */
class CourseController extends Controller
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
//        $courses = $this->getDoctrine()->getRepository(Course::class)->findAll();

        $page = $request->query->get('page');
        if($page == null)
            $page = 1;
        $pages['currentPage'] = (int) $page;
        $pages['limit'] = 1;
        $courses = $this->getDoctrine()->getRepository(Course::class)->paginate($pages['currentPage'],$pages['limit']);
        $totalStudents = count($this->getDoctrine()->getRepository(Course::class)->findAll());

        $pages['totalPages'] = ceil($totalStudents/$pages['limit']);

        $filterForm = $this->createFormBuilder()
            ->setMethod('GET')
            ->add('name', TextType::class)
            ->add('filter', SubmitType::class)
//            ->setAction($this->generateUrl('course.filter'))

            ->getForm();

        $filterForm->handleRequest($request);

        if($filterForm->isSubmitted() && $filterForm->isValid()){
            $name = $filterForm->getData();
            $courses = $this->getDoctrine()->getRepository(Course::class)->findBy(['name' => $name]);

        }

        return $this->render('course/index.html.twig', [
            'courses' => $courses,
            'filterForm' => $filterForm->createView(),
            'pages' => $pages,
        ]);
    }

    /**
     * @Route("/details/{id}", name = "details")
     * @param $id
     * @param CourseRepository $courseRepository
     * @return Response
     */
    public function details($id, CourseRepository $courseRepository){
        $course = $courseRepository->find($id);

        return $this->render('course/details.html.twig',[
            'course'=>$course,
        ]);
    }

    /**
     * @Route("/create" , name = "create")
     * @param Request $request
     * @return Response
     */
    public function createCourse(Request $request){
        $course = new Course();

        $form = $this->createFormBuilder($course)
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('classe', EntityType::class, [
                'class' => Classe::class,
                'choice_label' => function(?Classe $classe){
                    return $classe  ? $classe -> getName() : '';
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Classes'
            ])
            ->add('save',SubmitType::class, ['label'=>'Create'])
            ->getForm();

        $form->handleRequest($request);

        $classes = $course->getClasse();

        if($form->isSubmitted() && $form->isValid()){
            $course = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($course);
            foreach ($classes as $classe){
                $classe->getCourses()->add($course);
            }
            $em->flush();

            $this->addFlash(
                'success',
                'Course was created'
            );
            return $this->redirectToRoute('course.index');
        }

        return $this->render('course/course_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Create'
        ]);
    }


    /**
     * @Route("/edit/{id}" , name = "edit")
     * @param Course $course
     * @param Request $request
     * @return Response
     */
    public function editCourse(Course $course,Request $request){

        $form = $this->createFormBuilder($course)
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('classe', EntityType::class, [
                'class' => Classe::class,
                'choice_label' => function(?Classe $classe){
                    return $classe  ? $classe -> getName() : '';
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Classes'
            ])
            ->add('save',SubmitType::class, ['label'=>'Edit'])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $classes = $course->getClasse();
            $course = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($course);
            foreach ($classes as $classe){
                $classe->getCourses()->add($course);
            }
            $em->flush();

            $this->addFlash(
                'success',
                'Course was edited'
            );

            return $this->redirectToRoute('course.index');
        }

        return $this->render('course/course_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Edit',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @param Course $course
     * @return RedirectResponse
     */
    public function delete(Course $course){
        $em = $this->getDoctrine()->getManager();
        $em->remove($course);
        $em->flush();
        $this->addFlash(
            'success',
            'Course was removed'
        );
        return $this->redirectToRoute('course.index');
    }

    /**
     * @Route("/", name = "filter", Methods = "GET")
     * @param Request $request
     * @return Response
     */
    public function filter(Request $request){
//        $course = $request->query->get();
//        $courses = $this->getDoctrine()->getRepository(Course::class)->findBy(['name' => $course]);
        $request->query->get('name');
//        return $this->render('course/index.html.twig', [
////            'courses' => $courses,
//        ]);
        return new Response("test");
    }


//    /**
//     * @Route("/grade/{id}" ,name = "grade")
//     * @param $id
//     * @param Request $request
//     * @return Response
//     */
//    public function createGrade($id,Request $request){
//        $student = $this->getDoctrine()->getRepository(Student::class)->find($id);
//        $courses = $student->getClasse()->getCourses();
//        $grade = new Grade();
//        $grade->setStudent($student);
////
//        $form = $this->createFormBuilder($student->getClasse())
//
//            ->getForm();
//
//
//        return $this->render('course/grade.html.twig',[
//            'form' => $form->createView(),
//            'student' => $student,
//            'courses' => $student->getClasse()->getCourses(),
//        ]);
//    }

}
