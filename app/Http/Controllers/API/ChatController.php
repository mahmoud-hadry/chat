<?php

namespace App\Http\Controllers\API;

use App\Models\Doctor;
use App\Models\Message;
use App\Models\Patient;
use App\Models\Visit;
use App\models\User;
use Carbon\Carbon;
use Chatify\Facades\ChatifyMessenger as Chatify;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Object_;

class ChatController extends Controller
{
    private function prepareResult($status, $data, $errors = '', $msg = '', $code = '200') {
        if ($status == true) {
            $return = array('status'=>$status,'code'=>$code,'data' => $data);
        } else {
            if (is_string($errors)) {
                $message = $errors;
            } else {
                $message = $errors->first();
            }
            $return = array('status'=>$status,'code'=>$code,'data' => $data, 'message_en' => $message, 'message_ar' => $message, 'errors' => '');
        }
        return response()->json($return, $code);
    }
    public function validations($request, $type) {
        $errors = [];
        $error = false;
        if ($type == "available") {
            $validator = Validator::make($request->all(), [
                'patientId' => 'required|int',
            ]);
            if ($validator->fails()) {
                $error = true;
                $errors = $validator->errors();
            }
        }
        if ($type == "chatsList") {
            $validator = Validator::make($request->all(), [
                'patientId' => 'required|int',
            ]);
            if ($validator->fails()) {
                $error = true;
                $errors = $validator->errors();
            }
        }if ($type == "chat") {
            $validator = Validator::make($request->all(), [
                'patientId' => 'required|int',
                'doctorId' => 'required|int',
            ]);
            if ($validator->fails()) {
                $error = true;
                $errors = $validator->errors();
            }
        }
        if ($type == "send") {
            if ($request->type != "text") {
                $validator = Validator::make($request->all(), [
                // 'patientId' => 'required|int',
                'from_id' => 'required|int',
                'to_id' => 'required|int',
                'type' => 'required',
                'file' => 'required',
            ]);
            }else{
                $validator = Validator::make($request->all(), [
                    // 'patientId' => 'required|int',
                    'from_id' => 'required|int',
                    'to_id' => 'required|int',
                    'type' => 'required',
                    'message' => 'required',
                ]);

            }

            if ($validator->fails()) {
                $error = true;
                $errors = $validator->errors();
            }
        }

        return array("error" => $error, "errors" => $errors);
    }

    public function available(Request $request){
    // Validate request
    $error = $this->validations($request, "available");
    if ($error['errors']) {
        return $this->prepareResult(false, [], $error['errors'], [
            'en' => 'Error in data', 
            'ar' => 'خطأ في البيانات'
        ], '400');
    }

    // Validate patient existence
    $patient = Patient::find($request->patientId);
    if (!$patient) {
        return $this->prepareResult(false, [], 'invalid patientId', 'patient not exist', '400');
    }

    // Select fields based on language
    $isArabic = $request->header('accept-language') === 'ar';
    $fnameField = $isArabic ? 'users.fname_ar AS fname' : 'users.fname';
    $lnameField = $isArabic ? 'users.lname_ar AS lname' : 'users.lname';

    // Query to fetch doctors and promoted doctors
    $doctors = Visit::where('visits.patient_id', $request->patientId)
        ->leftJoin('doctors', 'doctors.id', '=', 'visits.doctor_id')
        ->leftJoin('users', 'users.id', '=', 'doctors.user_id')
        ->leftJoin('messages', function ($join) {
            $join->on('messages.from_id', '=', 'users.id')
                 ->orWhere('messages.to_id', '=', 'users.id');
        })
        ->select('users.id AS doctor_user_id', 'doctors.id AS doctorId', 'doctors.photo_path', 
                 $fnameField, $lnameField, 'users.active_status', 'doctors.promotion')
        ->whereNotNull('doctors.id')
        ->orderBy('users.active_status')
        ->orderBy('doctors.promotion', 'desc')
        ->distinct();

    // Add promoted doctors to the same query
    $promotedDoctors = Doctor::where('doctors.promotion', 1)
        ->leftJoin('users', 'users.id', '=', 'doctors.user_id')
        ->select('users.id AS doctor_user_id', 'doctors.id AS doctorId', 'doctors.photo_path', 
                 $fnameField, $lnameField, 'users.active_status', 'doctors.promotion');

    // Merge both results
    $doctors = $doctors->union($promotedDoctors)->get();

    // Update photo paths and return results
    $doctors->each(function ($doctor) {
        $doctor->photo_path = env('APP_URL') . ltrim($doctor->photo_path, '/');
    });

    // Return response
    return $doctors->isEmpty()
        ? $this->prepareResult(false, $doctors, 'no_data', 'no data')
        : $this->prepareResult(true, $doctors, 'no_errors', 'true');
    }

   
public function chatsList(Request $request){
    // Validate request
    $error = $this->validations($request, "chatsList");
    if ($error['errors']) {
        return $this->prepareResult(false, [], $error['errors'], [
            'en' => 'Error in data', 
            'ar' => 'خطأ في البيانات'
        ], '400');
    }

    // Get patient user id
    $patientUser = Patient::where('patients.id', $request->patientId)
        ->leftJoin('users', 'users.id', 'patients.user_id')
        ->select('users.id')
        ->first();
    if (!$patientUser) {
        return $this->prepareResult(false, [], 'invalid patientId', 'invalid id', '400');
    }

    // Define language fields
    $isArabic = $request->header('accept-language') === 'ar';
    $fnameField = $isArabic ? 'users.fname_ar AS fname' : 'users.fname';
    $lnameField = $isArabic ? 'users.lname_ar AS lname' : 'users.lname';

    // Fetch chats
    $chats = \Chatify\Http\Models\Message::join('users', function ($join) use ($patientUser) {
            $join->on('messages.from_id', '=', 'users.id')
                 ->orOn('messages.to_id', '=', 'users.id');
        })
        ->where('users.user_type_id', 1)
        ->where(function ($query) use ($patientUser) {
            $query->where('messages.from_id', $patientUser->id)
                  ->orWhere('messages.to_id', $patientUser->id);
        })
        ->orderBy('messages.created_at', 'desc')
        ->join('doctors', 'doctors.user_id', 'users.id')
        ->select('users.id AS doctor_user_id', 'doctors.id AS doctorId', 'doctors.photo_path', 
                 $fnameField, $lnameField, 'messages.body', 'messages.from_id', 'messages.to_id', 
                 'messages.id', 'messages.seen', 'messages.created_at AS lastMessageTime', 
                 'users.active_status', 'doctors.promotion')
        ->get()
        ->unique('doctor_user_id')
        ->values();

    // Process each chat
    foreach ($chats as $chat) {
        $chat->photo_path = env('APP_URL') . $chat->photo_path;
        // Format time
        $messageDate = Carbon::parse($chat->lastMessageTime)->format('Y-m-d');

        if ($messageDate === Carbon::today()->format('Y-m-d')) {
                    $chat->day = 'Today';
                } elseif ($messageDate === Carbon::yesterday()->format('Y-m-d')) {
                    $chat->day = 'Yesterday';
                } else {
                    $chat->day = 'null';
                }
                    
            }

    // Return the response
    return $chats->isEmpty()
        ? $this->prepareResult(false, $chats, 'no_data', 'no data', '200')
        : $this->prepareResult(true, $chats, 'no_errors', 'true', '200');
        }

    
    public function chatMessages(Request $request){

        $error = $this->validations($request, "chatMessages");
        if ($error['errors']) {
            return $this->prepareResult(false, [], $error['errors'], array('en' => 'Error in data', 'ar' => 'خطأ في البيانات'), '400');
        }
        $patientUser = Patient::where('patients.id', $request->patientId)
            ->leftJoin('users', 'users.id', 'patients.user_id')
            ->select('users.id')
            ->first();
        if ($patientUser == null){
            return $this->prepareResult(true, [], 'patient not exist', 'no data' ,'200');

        }
        $doctorUser = Doctor::where('doctors.id', $request->doctorId)
            ->leftJoin('users', 'users.id', 'doctors.user_id')
            ->where('user_type_id', 1)
            ->select('users.id')
            ->first();

        if ($doctorUser == null){
            return $this->prepareResult(false, [], 'doctor not exist', 'no data' ,'200');

        }

        $messages = Message::where('from_id',$patientUser->id)->where('to_id',$doctorUser->id)
            ->orWhere('from_id',$doctorUser->id)->where('to_id',$patientUser->id)

            ->leftjoin('users', function($join) use ($doctorUser, $patientUser) {
                $join->where('users.id', '=','messages.form_id');
                $join->orWhere('users.id', '=','messages.to_id');
                $join->where('users.id', '=', $doctorUser->id);
            })
            ->leftjoin('doctors', function($join){
                $join->on('messages.from_id', '=','doctors.user_id');
                $join->orOn('messages.to_id', '=','doctors.user_id');
            })
            ->leftJoin('users As du', 'du.id', 'doctors.user_id')
            ->orderBy('messages.created_at', 'desc');


        if ($request->header('accept-language') == 'ar') {
            $messages = $messages->select('du.id As doctor_user_id', 'doctors.id As doctorId', 'doctors.photo_path','messages.id As messageId', 'messages.from_id As from_user_id', 'messages.to_id As to_user_id', 'du.fname_ar As fname', 'du.lname_ar As lname', 'messages.body','messages.attachment', 'messages.seen','messages.created_at As time', 'du.active_status');
        }else{
            $messages = $messages->select('du.id As doctor_user_id', 'doctors.id As doctorId', 'doctors.photo_path','messages.id As messageId', 'messages.from_id As from_user_id', 'messages.to_id As to_user_id', 'du.fname', 'du.lname', 'messages.body', 'messages.attachment','messages.seen','messages.created_at As time', 'du.active_status');

        }
           $messages = $messages->paginate(30)->reverse()->values();
         $allowed_images = array('png','jpg','jpeg','gif');
         $allowed_files  = array('zip','rar','txt', 'doc');
        foreach ($messages as $message){
            $default_image = env('APP_URL') . ltrim('/uploads/doctors/defaultDoctorImage.png');
            if ($message->photo_path != "" && $message->photo_path != null) {
                if (file_exists(public_path($message->photo_path))) {
                    $message->photo_path = env('APP_URL') . ltrim($message->photo_path);

                }
            } else {
                $message->photo_path = $default_image;
            }
            $message->type = "text";
            if ($message->attachment != null){
                if (strpos($message->attachment, 'png')|| strpos($message->attachment, 'Jpg') || strpos($message->attachment,  'Jpeg') ||strpos($message->attachment,  'gif') !== false) {
                    $message->type = "image";
                }
                else{
                    $message->type = "file";

                }
                $message->body = env('APP_URL').'/storage/attachments/'.substr($message->attachment, 0, strpos($message->attachment, ","));

            }
        }
        if ($messages->count() == 0){
            return $this->prepareResult(false, $messages, 'no_data', 'no data' ,'200');

        }else {
            return $this->prepareResult(true, $messages, 'no_errors', 'true', '200');
        }
    }
  

    public function send(Request $request){
    // Validate request data
    $error = $this->validations($request, "send");
    if ($error['errors']) {
        return $this->prepareResult(false, [], $error['errors'], [
            'en' => 'Error in data', 
            'ar' => 'خطأ في البيانات'
        ], '400');
    }

    // Handle attachment if not a text message
    $attachment = $attachment_title = $error_msg = null;
    if ($request->type !== "text") {
        $attachmentResult = $this->handleFileUpload($request);
        if (isset($attachmentResult['error'])) {
            return $this->prepareResult(false, [], [], $attachmentResult['error'], '400');
        }
        $attachment = $attachmentResult['attachment'];
        $attachment_title = $attachmentResult['attachment_title'];
        $request['message'] = env('APP_URL') . '/storage/attachments/' . $attachment;
    }

    // Send the message to the database
    $messageID = $this->storeMessage($request, $attachment, $attachment_title);

    // Push the message using Pusher and set up response data
    $responseData = $this->pushMessageAndPrepareData($request, $messageID);

    // Send the response
    return $this->prepareResult(true, $responseData, 'no_errors', 'true', '200');
}

/**
 * Handles file upload logic.
 */
private function handleFileUpload(Request $request)
{
    if (!$request->hasFile('file')) {
        return ['error' => 'No file uploaded.'];
    }

    $allowed_images = ['png', 'jpg', 'jpeg', 'gif'];
    $allowed_files = ['zip', 'rar', 'txt', 'pdf', 'doc', 'docx'];
    $allowed_extensions = array_merge($allowed_images, $allowed_files);

    $file = $request->file('file');

    // Check file size (less than 150MB)
    if ($file->getSize() >= 150000000) {
        return ['error' => 'File size is too large!'];
    }

    // Check file extension
    if (!in_array($file->getClientOriginalExtension(), $allowed_extensions)) {
        return ['error' => 'File extension not allowed!'];
    }

    // Store the file and return the new attachment details
    $attachment_title = $file->getClientOriginalName();
    $attachment = rand() . '.' . $file->getClientOriginalExtension();
    $file->storeAs("public/" . config('chatify.attachments.folder'), $attachment);

    return ['attachment' => $attachment, 'attachment_title' => $attachment_title];
}

/**
 * Stores the new message in the database.
 */
private function storeMessage(Request $request, $attachment, $attachment_title)
{
    $messageID = mt_rand(9, 999999999) + time();

    Chatify::newMessage([
        'id' => $messageID,
        'type' => "user",
        'from_id' => $request['from_id'],
        'to_id' => $request['to_id'],
        'body' => $request['message'],
        'attachment' => ($attachment) ? $attachment . ',' . $attachment_title : null,
    ]);

    return $messageID;
}

/**
 * Pushes the message using Pusher and prepares response data.
 */
private function pushMessageAndPrepareData(Request $request, $messageID)
{
    $user = User::find($request['to_id']);
    $doctor = Doctor::where('user_id', $request['to_id'])->first();

    $doctorId = $doctor->id ?? null;
    $photoPath = $doctor ? env('APP_URL') . $doctor->photo_path : null;

    $pushData = [
        'doctor_user_id' => $request['to_id'],
        'doctorId' => $doctorId,
        'from_user_id' => $request['from_id'],
        'to_user_id' => $request['to_id'],
        'body' => $request['message'],
        'fname' => $user->fname,
        'lname' => $user->lname,
        'photo_path' => $photoPath,
        'seen' => 0,
        'time' => Carbon::now(),
        'active_status' => 1,
        'messageId' => $messageID,
        'type' => $request->type,
    ];

    Chatify::push('chatify' . (int)$request['to_id'], 'messaging', $pushData);

    // Prepare data for the response
    $responseData = new Object_();
    foreach ($pushData as $key => $value) {
        $responseData->$key = $value;
    }
    $responseData->time = Carbon::now()->format('Y-m-d H:i:s');

    return $responseData;
}
}
