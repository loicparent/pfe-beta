<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Course;
use App\User;
use App\Seance;
use App\Work;
use App\Test;
use \Input;
use App\Notification;
use Carbon\Carbon;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{

    protected $rules = [
        'title' => 'required|max:255',
        'group' => 'required|max:255',
        'school' => 'required|max:255',
        'place' => 'required|max:255'
        ];

    public function index() {
        $title = 'Accueil';
        if ( \Auth::check() ) {
            $title = 'Tous mes cours';
            if ( \Auth::user()->status === 1 ) {
                $courses = Course::where( 'teacher_id', '=', \Auth::user()->id )->get();
                return view('courses/indexTeacherCourses', compact('courses', 'title'));
            }
            return view('courses/indexStudentCourses', compact('courses', 'title'));
        } 
        return view('welcome', ['title' => $title]);
    }

    public function view( $id ) {
        setlocale( LC_ALL, 'fr_FR');
        $course = Course::findOrFail($id);
        $teacher = User::where( 'id', '=', $course->teacher_id )->get();
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $seances = $course->seances->sortBy('start_hours');
        $students = Course::find($id)->users;

        $inCourseStudents = [];
        $demandedStudents = [];
        $inCourseStudentsId = [];
        $demandedStudentsId = [];
        foreach ($students as $student) {
            if( $student->pivot->access === 1 ){
                $demandedStudents[] = $student;
                $demandedStudentsId[] = $student->id;
            }
            if( $student->pivot->access === 2 ){
                $inCourseStudents[] = $student;
                $inCourseStudentsId[] = $student->id;
            }
        }

        $title = 'Cours de '.$course->title;
        if ( \Auth::user()->status == 1 ) {
            $title = 'Cours de '.$course->title.' groupe '. $course->group;

            return view('courses/viewCourse', compact('id', 'course', 'title', 'seances', 'demandedStudents', 'inCourseStudents', 'demandedStudentsId', 'inCourseStudentsId'));
        }

        return view('courses/viewCourse', compact('course', 'teacher', 'title', 'seances', 'demandedStudentsId', 'inCourseStudentsId'));
    }

    public function create() {
        $title = 'Créer un cours';

        return view('courses/createCourse', ['title' => $title]);
    }

    public function store() {
        $validator = Validator::make(Input::all(), $this->rules);
        if ($validator->fails()) {
            //echo $validator->messages('title');
            return redirect()->back();
        }
        $courses = Course::all();
        $course = Course::create([
            'title' => Input::get('title'),
            'teacher_id' => \Auth::user()->id,
            'access_token' => substr( md5(Carbon::now() ), 0, 6),
            'group' => Input::get('group'),
            'school' => Input::get('school'),
            'place' => Input::get('place'),
            ]);
        
        return redirect()->route('indexCourse');
    }

    public function searchCourse() {
        if ( \Auth::check() && \Auth::user()->status==2 ) {
            $coursesIds = \Auth::user()->courses->lists('id');
            $title = 'Tous les cours existants';
            $courses = Course::whereNotIn('id',$coursesIds)->get();

            return view('courses/indexAllCourses', compact('courses', 'title'));
        } 
        return back();
    }

    public function addCourse( $id ) {
        if ( \Auth::check() && \Auth::user()->status==2 ) {
            $student = User::findOrFail(\Auth::user()->id);

            // Ajouter le cours et l'utilisateur à la table course_user
            $student->courses()->attach( $id );
            \DB::table('course_user')
                ->where('user_id', \Auth::user()->id)->where('course_id', $id)
                ->update(array('access' => 1));

            Notification::create([
                'title' => 'demande accès au cours de',
                'course_id' => $id,
                'user_id' => \Auth::user()->id,
                'context' => 5,
                'seen' => 0,
                'for' => Course::where('id', $id)->get()->first()->teacher_id
            ]);
            
            return redirect()->route('indexCourse');
        } 
        return back();
    }

    public function addNews() {
        if ( \Auth::check() && \Auth::user()->status==1 ) {
            $title = 'Ajouter une notification';
            return view('courses/addNews', compact('title'));
        } 
        return back();
    }

    public function getByToken() {
        $token = Input::get('searchToken');
        $course_id = Course::where( 'access_token', '=', $token )->get()->first()->id;
        $student = User::findOrFail(\Auth::user()->id);

        // Ajouter le cours et l'utilisateur à la table course_user
        $student->courses()->attach( $course_id );
        \DB::table('course_user')
            ->where('user_id', \Auth::user()->id)->where('course_id', $course_id)
            ->update(array('access' => 1));

        return redirect()->route('indexCourse');
    }

    public function removeCourse( $id_course ) {
        \DB::table('course_user')
        ->where('user_id', \Auth::user()->id)->where('course_id', $id_course)->delete();

        Notification::create([
            'title' => 'à quiter le cours de',
            'course_id' => $id_course,
            'user_id' => \Auth::user()->id,
            'context' => 8,
            'seen' => 0,
            'for' => Course::where('id', $id_course)->get()->first()->teacher_id
        ]);

        return redirect()->route('indexCourse');
    }

    public function acceptStudent( $id_course, $id_user ) {
        \DB::table('course_user')
        ->where('user_id', $id_user)
        ->where('course_id', $id_course)
        ->update(array('access' => 2));

        return redirect()->route('viewCourse', ['id' => $id_course, 'action' => 1]);
    }
    
    public function removeStudentFromCourse( $id_course, $id_user ) {
        \DB::table('course_user')
        ->where('user_id', $id_user)->where('course_id', $id_course)->delete();

        return redirect()->route('viewCourse', ['id' => $id_course, 'action' => 1]);
    }

    public function edit( $id ) {
        $course = Course::findOrFail($id);
        $pageTitle = 'Modifier le cours';
        $id = $course->id;
        $title = $course->title;
        $group = $course->group;
        $school = $course->school;
        $place = $course->place;
        return view('courses/updateCourse', compact('pageTitle', 'id', 'title', 'group', 'school', 'place'));
    }

    public function update( $id ) {
        $validator = Validator::make(Input::all(), $this->rules);
        if ($validator->fails()) {
            //echo $validator->messages('title');
            return redirect()->back();
        }
        $course = Course::findOrFail($id);
        $course->title = Input::get('title');
        $course->group = Input::get('group');
        $course->school = Input::get('school');
        $course->place = Input::get('place');
        $course->updated_at = Carbon::now();
        $course->save();
        return redirect()->route('indexCourse');
    }

    public function delete( $id ) {
        $course = Course::findOrFail($id);
        $seances = Seance::where( 'course_id', '=', $course->id )->get();;
        foreach ($seances as $seance) {

            $works = Work::where( 'seance_id', '=', $seance->id )->get();
            foreach ($works as $work) {
                $work->delete();   
            }

            $tests = Test::where( 'seance_id', '=', $seance->id )->get();
            foreach ($tests as $test) {
                $test->delete();   
            }

            $seance->delete();   
        }
        $course->delete();
        return redirect()->route('indexCourse');
    }
}
