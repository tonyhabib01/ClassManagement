<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Entity\Course;
use App\Entity\Student;
use App\Repository\ClasseRepository;
use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StudentController
 * @package App\Controller
 * @Route("/student", name="student.")
 */
class StudentController extends Controller
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {

//        $students = $this->getDoctrine()->getRepository(Student::class)->findAll();
        $page = $request->query->get('page');
        if($page == null)
            $page = 1;
        $pages['currentPage'] = (int) $page;
        $pages['limit'] = 1;
        $students = $this->getDoctrine()->getRepository(Student::class)->paginate($pages['currentPage'],$pages['limit']);
        $totalStudents = count($this->getDoctrine()->getRepository(Student::class)->findAll());

        $pages['totalPages'] = ceil($totalStudents/$pages['limit']);

        $filterForm = $this->createFormBuilder()
            ->setMethod('GET')
            ->add('firstName', TextType::class , ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('course', TextType::class, ['required' => false])
            ->add('filter', SubmitType::class)
            ->getForm();

        $filterForm->handleRequest($request);

        if($filterForm->isSubmitted() && $filterForm->isValid()){
            $filteredStudents = $filterForm->getData();
            $filterData = array();
            foreach ($filteredStudents as $i => $i_value){
                if ($i_value != null && $i != 'course')
                    $filterData[$i] = $i_value;
            }
            if (sizeof($filterData) != 0 || $filteredStudents['course'] != null) {
                if ($filteredStudents['course'] != null){
                    $course = $this->getDoctrine()->getRepository(Course::class)->findOneBy(['name' => $filteredStudents['course']]);

                    if($course != null){
                        $classes = $course->getClasse()->getValues();
                        dump($classes);
                        $filterData['classe'] = $classes;
                    }

                }
                $students = $this->getDoctrine()->getRepository(Student::class)->findBy($filterData);


            }

        }


        return $this->render('student/index.html.twig', [
            'students' => $students,
            'filterForm' => $filterForm->createView(),
            'pages' => $pages,
        ]);
    }

    /**
     * @Route("/details/{id}", name="details")
     * @param $id
     * @param StudentRepository $studentRepository
     * @return null
     */
    public function details($id, StudentRepository $studentRepository){
        $student = $studentRepository->find($id);
//        $averageGrade = 0.0;
//
//        if(sizeof($student->getGrades()) != 0){
//
//            //Retrieving and adding all course grades of this student
//            foreach ($student->getGrades() as $grade){
//                $averageGrade += $grade->getGrade();
//            }
//
//            //Calculating the average grade of this student
//            $averageGrade /= sizeof($student->getGrades());
//        }



        return $this->render('student/details.html.twig', [
            'student' => $student,
//            'grade' => $averageGrade
        ]);
    }

    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @return Response
     */
    public function createStudent(Request $request){
        $student = new Student();
        $classes = $this->getDoctrine()->getRepository(Classe::class)->findAll();

        $form = $this->createFormBuilder($student)
            ->add('firstName', TextType::class, ['label' => 'First Name'])
            ->add('lastName', TextType::class, ['label' => 'Last Name'])
            ->add('dateOfBirth',BirthdayType::class,['label' => 'Date of Birth'])
            ->add('classe', ChoiceType::class, [

                'choices' => $classes,
                'choice_value' => 'id',
                'choice_label' => function(?Classe $classe){
                    return $classe ? $classe->getName():'';
                },
                'label' => 'Class'
            ])
            ->add('save', SubmitType::class, ['label' => 'Create'])
            ->getForm();


        $form->handleRequest($request);



        if($form->isSubmitted() && $form->isValid()){
            $student = $form->getData();
            $em = $this->getDoctrine()->getManager();

            if($student->getGpa() == null)
                $student->setGpa(0.0);

            $em->persist($student);
            $em->flush();

            $this->addFlash(
                'success',
                'Student was created'
            );
            return $this->redirectToRoute('student.index');
        }

        return $this->render('student/student_form.html.twig',[
            'form'=>$form->createView(),
            'title' => 'Create'
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     * @param Student $student
     * @param Request $request
     * @return Response
     */
    public function editStudent(Student $student, Request $request){

        $classes = $this->getDoctrine()->getRepository(Classe::class)->findAll();
        $form = $this->createFormBuilder($student)
            ->add('firstName', TextType::class, ['label' => 'First Name'])
            ->add('lastName', TextType::class, ['label' => 'Last Name'])
            ->add('dateOfBirth',BirthdayType::class,['label' => 'Date of Birth'])
            ->add('classe', ChoiceType::class, [

                'choices' => $classes,
                'choice_value' => 'id',
                'choice_label' => function(?Classe $classe){
                    return $classe ? $classe->getName():'';
                },
                'label' => 'Class'
            ])
            ->add('save', SubmitType::class, ['label' => 'Edit'])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash(
                'success',
                'Student was edited'
            );
            return $this->redirectToRoute('student.index');
        }

        return $this->render('student/student_form.html.twig',[
            'form'=>$form->createView(),
            'title' => 'Edit'
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @param Student $student
     * @return RedirectResponse
     */
    public function delete(Student $student){
        $em = $this->getDoctrine()->getManager();
        $em->remove($student);
        $em->flush();

        $this->addFlash(
            'success',
            'Student was removed'
        );
        return $this->redirectToRoute('student.index');
    }

//    /**
//     * @Route("/test", name="test")
//     * @param $page
//     * @return Response
//     */
//    public function test($page){
//        dump($page);
//        $pages['currentPage'] = $page;
//        $pages['limit'] = 1;
//        $students = $this->getDoctrine()->getRepository(Student::class)->test($pages['currentPage'],$pages['limit']);
//        $totalStudents = count($this->getDoctrine()->getRepository(Student::class)->findAll());
//
//        $pages['totalPages'] = ceil($totalStudents/$pages['limit']);
//
//        return $this->render('student/index.html.twig',[
//            'students' => $students,
//            'pages' => $pages,
//        ]);
//    }

}
