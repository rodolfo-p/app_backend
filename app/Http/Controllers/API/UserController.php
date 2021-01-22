<?php

namespace App\Http\Controllers\API;


use App\Http\Data\Setup\SegRolUsuario;
use App\Models\Configuracion\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use Validator;
use App\Notifications\SignupActivate;
use Laravel\Passport\Passport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\RequestPassportEmail;
use App\Http\Data\util\IdGenerador;

class UserController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'c_password' => 'required|same:password',
        ]);
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'activation_token' => str_random(60)
        ]);
        $user->save();
        $user->notify(new SignupActivate($user));
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = request(['email', 'password']);
        //dd($credentials);
        $credentials['active'] = 1;
        $credentials['deleted_at'] = null;
        if (!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Passport::tokensExpireIn(Carbon::now()->addDays(15));
        $token->save();
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }


    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function signupActivate($token)
    {
        //dd($token);
        $user = User::where('activation_token', $token)->first();
        if (!$user) {
            return response()->json([
                'message' => 'This activation token is invalid.'
            ], 404);
        }
        $user->active = true;
        $user->activation_token = '';
        $user->save();
        return $user;
    }

    public function list_users()
    {
        return response()->json(['success' => true,
            'data' => User::whereNotIn('id', ['1', '2', '4', '5', '6'])->get(),
            'message' => 'Lista de Usuarios'], 200);
    }


    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(['success' => true,
            'data' => User::whereNotIn('id', ['1', '2', '4', '5', '6'])->get(),
            'message' => 'Lista de Usuarios'], 200);
    }

    public function request_passport(Request $request)
    {
        $email = $request->input('email');
        $sql = "select name, email from users where email= '" . $email . "'";
        $Query = DB::select($sql);
        if (count($Query) >= 1) {
            $token = str_random(64);
            $url_base = $request->header('Origin');
            $url = $url_base . '/reset/' . $token;
            DB::table('usuario_recuperar_contra')->insert(
                array('usuario_recuperar_contra_id' => IdGenerador::generaId(),
                    'usuario_recuperar_contra_email' => $email,
                    'usuario_recuperar_contra_token' => $token,
                    'usuario_recuperar_contra_fecha' => date('Y-m-d H:i:s'))
            );
            $requestPassportEmail = new \stdClass();
            $requestPassportEmail->sender = $Query[0]->name;
            $requestPassportEmail->email = $email;
            $requestPassportEmail->url = $url;
            Mail::to($email)->send(new RequestPassportEmail($requestPassportEmail));
            $jResponse['success'] = true;
            $jResponse['data'] = 'Correo Enviado: revise su correo para restaurar su contraseña';
            return response()->json($jResponse, 201);

        } else {
            $jResponse['success'] = false;
            $jResponse['data'] = $this->index();
            return response()->json($jResponse, 201);
        }


    }

    public function cambiar_estado(Request $request, $id)
    {
        $active = $request->input('active');
        $query = "UPDATE users SET active = '$active'
                    WHERE id = '$id'";
        DB::update($query);
        $jResponse['success'] = true;
        $jResponse['data'] = 'Contraseña cambiado';
        return response()->json($jResponse, 201);


    }

    public function reset_password(Request $request, $token)
    {
        $password = $request->input('password');
        $confirmPassword = $request->input('confirmPassword');
        $sql = "select usuario_recuperar_contra_email, usuario_recuperar_contra_fecha, sysdate()
from usuario_recuperar_contra
where usuario_recuperar_contra_token= '" . $token . "'
 and usuario_recuperar_contra_fecha='" . date('Y-m-d') . "'";
        $Query = DB::select($sql);
        if ($password == $confirmPassword && count($Query) >= 1) {
            $password = bcrypt($password);
            $query = "UPDATE users SET password = '$password'
                    WHERE email = '" . $Query[0]->usuario_recuperar_contra_email . "'";
            DB::update($query);
            $jResponse['success'] = true;
            $jResponse['data'] = 'Contraseña cambiado';
            return response()->json($jResponse, 201);

        } else {
            $jResponse['success'] = false;
            $jResponse['data'] = 'No se logro recuperar su contraseña intente de nuevo';
            return response()->json($jResponse, 201);
        }

    }

    public function delete_user($id)
    {
        User::find($id)->delete();
        $jResponse['success'] = true;
        $jResponse['data'] = 'Usuario no Eliminado:';
        return response()->json($jResponse, 201);

    }

    public function buscar_usuario(Request $request)
    {
        $data = $request->input('data');
        try {
            $sql = "select name, email,
concat(name,' ', email) as  nombres
from users  where concat(name,' ', email) like '%" . $data . "%'";
            //dd($sql);
            $Query = DB::select($sql);

        } catch (\Exception $exception) {
            dd($exception);

        }
        return $Query;

    }

    public function usuarios_conectados()
    {
        try {
            $sql = "select a.id,a.user_id, a.created_at, b.email
                    from oauth_access_tokens a , users b
                    where a.revoked = 0 and a.user_id=b.id and b.id not in (1,2,4,5,6) order by a.created_at ";
            //dd($sql);
            $Query = DB::select($sql);

        } catch (\Exception $exception) {
            dd($exception);

        }
        $jResponse['success'] = true;
        $jResponse['data'] = $Query;
        return response()->json($jResponse, 201);
    }

    public function desconectar_usuario(Request $request, $id)
    {

        $query = "UPDATE oauth_access_tokens SET revoked = 1
                    WHERE id = '$id'";
        DB::update($query);
        $jResponse['success'] = true;
        $jResponse['data'] = $this->usuarios_conectados();
        return response()->json($jResponse, 201);
    }

    public function usuario_rol(Request $request)
    {
        $id = auth()->user()->id;
        try {
            $sql = "select seg_rol_id, seg_rol_nombre, seg_rol_estado
from seg_rol
where seg_rol_id = (select seg_rol_usuario.seg_rol_id from seg_rol_usuario where user_id = '$id')";
            //dd($sql);
            $Query = DB::select($sql);

        } catch (\Exception $exception) {
            dd($exception);

        }
        $jResponse['success'] = true;
        $jResponse['data'] = $Query[0];
        return response()->json($jResponse, 201);
    }

}
