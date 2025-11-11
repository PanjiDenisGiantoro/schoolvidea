<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Authentication"},
     *     summary="Login user",
     *     description="Authenticate user and return JWT token with role-based data",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","code","tahun"},
     *             @OA\Property(property="email", type="string", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="code", type="string", example="SCH001", description="Kode unit dari tabel units"),
     *             @OA\Property(property="tahun", type="string", example="2024", description="Tahun ajaran")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="profile", type="object", description="Siswa or Officer data based on role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|string|email",
            "password" => "required|string",
            "code" => "required|string",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation error",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        // Validasi kode unit
        $unit = \App\Models\Unit::where("code", $request->code)->first();
        if (!$unit) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Kode unit tidak valid",
                ],
                401,
            );
        }

        // Validasi tahun ajaran
        // $tahunAjaran = \App\Models\Tahun_ajaran::where('tahun', $request->tahun)->first();
        // if (!$tahunAjaran) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Tahun ajaran tidak valid'
        //     ], 401);
        // }

        $credentials = $request->only("email", "password");

        if (!($token = auth("api")->attempt($credentials))) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Email atau password salah",
                ],
                401,
            );
        }

        // Validasi unit_id user sesuai dengan kode unit
        $user = auth("api")->user();
        if ($user->unit_id != $unit->id) {
            auth("api")->logout();
            return response()->json(
                [
                    "success" => false,
                    "message" => "User tidak terdaftar di unit ini",
                ],
                401,
            );
        }

        // Ambil user yang baru login
        $user = auth("api")->user();

        // Load roles
        $user->load(["roles", "permissions"]);

        // Ambil data profile berdasarkan role
        $profile = null;
        $userType = null;

        // Cek apakah user memiliki role siswa
        if ($user->hasRole("siswa") || $user->siswa) {
            $userType = "siswa";
            $siswaQuery = \App\Models\Siswa::with([
                "kelas",
                "unit",
                "tahun_ajaran",
                "jurusan",
                "saldo",
            ])->where("user_id", $user->id);

            // Filter berdasarkan tahun jika parameter tahun dikirim
            // if ($request->tahun) {
            //     $siswaQuery->whereHas('tahun_ajaran', function($q) use ($request) {
            //         $q->where('tahun', $request->tahun);
            //     });
            // }

            $profile = $siswaQuery->first();

            if ($profile) {
                $profile = [
                    "id" => $profile->id,
                    "nisn" => $profile->nisn,
                    "nis" => $profile->nis,
                    "nama" => $profile->nama ?? $user->name,
                    "tempat_lahir" => $profile->tempat_lahir,
                    "tanggal_lahir" => $profile->tanggal_lahir,
                    "jenis_kelamin" => $profile->jenis_kelamin,
                    "agama" => $profile->agama,
                    "alamat" => $profile->alamat,
                    "no_hp" => $profile->no_hp,
                    "no_hp_ortu" => $profile->no_hp_ortu,
                    "nama_ortu" => $profile->nama_ortu,
                    "nik" => $profile->nik,
                    "rfid_no" => $profile->rfid_no,
                    "va_siswa" => $profile->va_siswa,
                    "bank" => $profile->bank,
                    "no_rekening" => $profile->no_rekening,
                    "image" => $profile->image,
                    "qrcode" => $profile->qrcode,
                    "status" => $profile->status,
                    "kelas" => $profile->kelas
                        ? [
                            "id" => $profile->kelas->id,
                            "nama_kelas" => $profile->kelas->nama_kelas,
                        ]
                        : null,
                    "unit" => $profile->unit
                        ? [
                            "id" => $profile->unit->id,
                            "nama_unit" => $profile->unit->nama_unit,
                        ]
                        : null,
                    // 'tahun_ajaran' => $profile->tahun_ajaran ? [
                    //     'id' => $profile->tahun_ajaran->id,
                    //     'tahun' => $profile->tahun_ajaran->tahun,
                    //     'semester' => $profile->tahun_ajaran->semester,
                    // ] : null,
                    "jurusan" => $profile->jurusan
                        ? [
                            "id" => $profile->jurusan->id,
                            "nama_jurusan" => $profile->jurusan->nama_jurusan,
                            "kode_jurusan" => $profile->jurusan->kode_jurusan,
                        ]
                        : null,
                    "saldo" => $profile->saldo
                        ? [
                            "saldo_akhir" => $profile->saldo->saldo_akhir ?? 0,
                            "status" => $profile->saldo->status,
                        ]
                        : null,
                ];
            }
        } else {
            // Jika bukan siswa, ambil data officer
            $userType = "officer";
            $officerQuery = \App\Models\Officer::with([
                "unit",
                "position",
                "tahunAjaran",
                "rolePetugas",
                "kelas",
            ])->where("user_id", $user->id);

            // Filter berdasarkan tahun jika parameter tahun dikirim
            // if ($request->tahun) {
            //     $officerQuery->whereHas('tahunAjaran', function($q) use ($request) {
            //         $q->where('tahun', $request->tahun);
            //     });
            // }

            $profile = $officerQuery->first();

            if ($profile) {
                $profile = [
                    "id" => $profile->id,
                    "nip" => $profile->nip,
                    "nuptk" => $profile->nuptk,
                    "nik" => $profile->nik,
                    "tempat_lahir" => $profile->tempat_lahir,
                    "tanggal_lahir" => $profile->tanggal_lahir,
                    "jenis_kelamin" => $profile->jenis_kelamin,
                    "agama" => $profile->agama,
                    "alamat" => $profile->alamat,
                    "no_hp" => $profile->no_hp,
                    "rfid_no" => $profile->rfid_no,
                    "no_kartu_rfid" => $profile->no_kartu_rfid,
                    "bank" => $profile->bank,
                    "no_rekening" => $profile->no_rekening,
                    "va_guru" => $profile->va_guru,
                    "image" => $profile->image,
                    "qr_code" => $profile->qr_code,
                    "status" => $profile->status,
                    "unit" => $profile->unit
                        ? [
                            "id" => $profile->unit->id,
                            "nama_unit" => $profile->unit->nama_unit,
                        ]
                        : null,
                    "position" => $profile->position
                        ? [
                            "id" => $profile->position->id,
                            "position_name" =>
                                $profile->position->position_name,
                        ]
                        : null,
                    // 'tahun_ajaran' => $profile->tahunAjaran ? [
                    //     'id' => $profile->tahunAjaran->id,
                    //     'tahun' => $profile->tahunAjaran->tahun,
                    //     'semester' => $profile->tahunAjaran->semester,
                    // ] : null,
                    "role_petugas" => $profile->rolePetugas
                        ? [
                            "id" => $profile->rolePetugas->id,
                            "name" => $profile->rolePetugas->name,
                        ]
                        : null,
                    "kelas_diampu" => $profile->kelas->map(function ($k) {
                        return [
                            "id" => $k->id,
                            "nama_kelas" => $k->nama_kelas,
                        ];
                    }),
                ];
            }
        }

        return response()->json([
            "success" => true,
            "access_token" => $token,
            "token_type" => "bearer",
            "expires_in" => auth("api")->factory()->getTTL() * 60,
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "username" => $user->username,
                "rfid_no" => $user->rfid_no,
                "roles" => $user->roles->pluck("name"),
                "permissions" => $user->getAllPermissions()->pluck("name"),
            ],
            "user_type" => $userType,
            "profile" => $profile,
            "unit" => [
                "id" => $unit->id,
                "nama_unit" => $unit->nama_unit,
                "code" => $unit->code,
            ],
            // 'tahun_ajaran' => [
            //     'id' => $tahunAjaran->id,
            //     'tahun' => $tahunAjaran->tahun,
            //     'semester' => $tahunAjaran->semester ?? null,
            // ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/login-nisn",
     *     tags={"Authentication"},
     *     summary="Login with NISN",
     *     description="Authenticate siswa using NISN, password, and unit code",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nisn","password","code"},
     *             @OA\Property(property="nisn", type="string", example="1234567890"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="code", type="string", example="SCH001", description="Kode unit dari tabel units")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="profile", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function loginWithNisn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nisn' => 'required|string',
            'password' => 'required|string',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validasi kode unit
        $unit = \App\Models\Unit::where('code', $request->code)->first();
        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Kode unit tidak valid'
            ], 401);
        }

        // Cari siswa berdasarkan NISN dan unit_id
        $siswa = \App\Models\Siswa::with([
            'user',
            'kelas',
            'unit',
            'tahun_ajaran',
            'jurusan',
            'saldo'
        ])
        ->where('nisn', $request->nisn)
        ->where('unit_id', $unit->id)
        ->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'NISN tidak ditemukan atau tidak terdaftar di unit ini'
            ], 401);
        }

        // Cek apakah siswa punya user
        if (!$siswa->user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun siswa belum terhubung dengan user'
            ], 401);
        }

        // Validasi password
        if (!Hash::check($request->password, $siswa->user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah'
            ], 401);
        }

        // Generate token dengan JWT
        $token = auth('api')->login($siswa->user);

        // Load user data
        $user = $siswa->user;
        $user->load(['roles', 'permissions']);

        // Format profile siswa
        $profile = [
            'id' => $siswa->id,
            'nisn' => $siswa->nisn,
            'nis' => $siswa->nis,
            'nama' => $siswa->nama ?? $user->name,
            'tempat_lahir' => $siswa->tempat_lahir,
            'tanggal_lahir' => $siswa->tanggal_lahir,
            'jenis_kelamin' => $siswa->jenis_kelamin,
            'agama' => $siswa->agama,
            'alamat' => $siswa->alamat,
            'no_hp' => $siswa->no_hp,
            'no_hp_ortu' => $siswa->no_hp_ortu,
            'nama_ortu' => $siswa->nama_ortu,
            'nik' => $siswa->nik,
            'rfid_no' => $siswa->rfid_no,
            'va_siswa' => $siswa->va_siswa,
            'bank' => $siswa->bank,
            'no_rekening' => $siswa->no_rekening,
            'image' => $siswa->image,
            'qrcode' => $siswa->qrcode,
            'status' => $siswa->status,
            'kelas' => $siswa->kelas ? [
                'id' => $siswa->kelas->id,
                'nama_kelas' => $siswa->kelas->nama_kelas,
            ] : null,
            'unit' => $siswa->unit ? [
                'id' => $siswa->unit->id,
                'nama_unit' => $siswa->unit->nama_unit,
            ] : null,
            'tahun_ajaran' => $siswa->tahun_ajaran ? [
                'id' => $siswa->tahun_ajaran->id,
                'tahun' => $siswa->tahun_ajaran->tahun,
                'semester' => $siswa->tahun_ajaran->semester,
            ] : null,
            'jurusan' => $siswa->jurusan ? [
                'id' => $siswa->jurusan->id,
                'nama_jurusan' => $siswa->jurusan->nama_jurusan,
                'kode_jurusan' => $siswa->jurusan->kode_jurusan,
            ] : null,
            'saldo' => $siswa->saldo ? [
                'saldo_akhir' => $siswa->saldo->saldo_akhir ?? 0,
                'status' => $siswa->saldo->status,
            ] : null,
        ];

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'rfid_no' => $user->rfid_no,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
            'user_type' => 'siswa',
            'profile' => $profile,
            'unit' => [
                'id' => $unit->id,
                'nama_unit' => $unit->nama_unit,
                'code' => $unit->code,
            ],
        ]);
    }

    /**
     * Register a new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "username" => "required|string|max:255|unique:users",
            "password" => "required|string|min:6|confirmed",
            "unit_id" => "nullable|exists:units,id",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validation error",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "username" => $request->username,
            "password" => Hash::make($request->password),
            "unit_id" => $request->unit_id,
        ]);

        $token = auth("api")->login($user);

        return response()->json(
            [
                "success" => true,
                "message" => "User registered successfully",
                "user" => $user,
                "token" => $token,
            ],
            201,
        );
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Authentication"},
     *     summary="Get authenticated user",
     *     description="Get current authenticated user details with profile data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="tahun",
     *         in="query",
     *         description="Filter by tahun ajaran (optional)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="user_type", type="string", example="siswa"),
     *             @OA\Property(property="profile", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function me(Request $request)
    {
        $user = auth("api")->user();
        $user->load(["roles", "permissions"]);

        // Ambil data profile berdasarkan role
        $profile = null;
        $userType = null;

        // Cek apakah user memiliki role siswa
        if ($user->hasRole("siswa") || $user->siswa) {
            $userType = "siswa";
            $siswaQuery = \App\Models\Siswa::with([
                "kelas",
                "unit",
                "tahun_ajaran",
                "jurusan",
                "saldo",
            ])->where("user_id", $user->id);

            // Filter berdasarkan tahun jika parameter tahun dikirim
            if ($request->tahun) {
                $siswaQuery->whereHas("tahun_ajaran", function ($q) use (
                    $request,
                ) {
                    $q->where("tahun", $request->tahun);
                });
            }

            $profile = $siswaQuery->first();

            if ($profile) {
                $profile = [
                    "id" => $profile->id,
                    "nisn" => $profile->nisn,
                    "nis" => $profile->nis,
                    "nama" => $profile->nama ?? $user->name,
                    "tempat_lahir" => $profile->tempat_lahir,
                    "tanggal_lahir" => $profile->tanggal_lahir,
                    "jenis_kelamin" => $profile->jenis_kelamin,
                    "agama" => $profile->agama,
                    "alamat" => $profile->alamat,
                    "no_hp" => $profile->no_hp,
                    "no_hp_ortu" => $profile->no_hp_ortu,
                    "nama_ortu" => $profile->nama_ortu,
                    "nik" => $profile->nik,
                    "rfid_no" => $profile->rfid_no,
                    "va_siswa" => $profile->va_siswa,
                    "bank" => $profile->bank,
                    "no_rekening" => $profile->no_rekening,
                    "image" => $profile->image,
                    "qrcode" => $profile->qrcode,
                    "status" => $profile->status,
                    "kelas" => $profile->kelas
                        ? [
                            "id" => $profile->kelas->id,
                            "nama_kelas" => $profile->kelas->nama_kelas,
                        ]
                        : null,
                    "unit" => $profile->unit
                        ? [
                            "id" => $profile->unit->id,
                            "nama_unit" => $profile->unit->nama_unit,
                        ]
                        : null,
                    "tahun_ajaran" => $profile->tahun_ajaran
                        ? [
                            "id" => $profile->tahun_ajaran->id,
                            "tahun" => $profile->tahun_ajaran->tahun,
                            "semester" => $profile->tahun_ajaran->semester,
                        ]
                        : null,
                    "jurusan" => $profile->jurusan
                        ? [
                            "id" => $profile->jurusan->id,
                            "nama_jurusan" => $profile->jurusan->nama_jurusan,
                            "kode_jurusan" => $profile->jurusan->kode_jurusan,
                        ]
                        : null,
                    "saldo" => $profile->saldo
                        ? [
                            "saldo_akhir" => $profile->saldo->saldo_akhir ?? 0,
                            "status" => $profile->saldo->status,
                        ]
                        : null,
                ];
            }
        } else {
            // Jika bukan siswa, ambil data officer
            $userType = "officer";
            $officerQuery = \App\Models\Officer::with([
                "unit",
                "position",
                "tahunAjaran",
                "rolePetugas",
                "kelas",
            ])->where("user_id", $user->id);

            // Filter berdasarkan tahun jika parameter tahun dikirim
            if ($request->tahun) {
                $officerQuery->whereHas("tahunAjaran", function ($q) use (
                    $request,
                ) {
                    $q->where("tahun", $request->tahun);
                });
            }

            $profile = $officerQuery->first();

            if ($profile) {
                $profile = [
                    "id" => $profile->id,
                    "nip" => $profile->nip,
                    "nuptk" => $profile->nuptk,
                    "nik" => $profile->nik,
                    "tempat_lahir" => $profile->tempat_lahir,
                    "tanggal_lahir" => $profile->tanggal_lahir,
                    "jenis_kelamin" => $profile->jenis_kelamin,
                    "agama" => $profile->agama,
                    "alamat" => $profile->alamat,
                    "no_hp" => $profile->no_hp,
                    "rfid_no" => $profile->rfid_no,
                    "no_kartu_rfid" => $profile->no_kartu_rfid,
                    "bank" => $profile->bank,
                    "no_rekening" => $profile->no_rekening,
                    "va_guru" => $profile->va_guru,
                    "image" => $profile->image,
                    "qr_code" => $profile->qr_code,
                    "status" => $profile->status,
                    "unit" => $profile->unit
                        ? [
                            "id" => $profile->unit->id,
                            "nama_unit" => $profile->unit->nama_unit,
                        ]
                        : null,
                    "position" => $profile->position
                        ? [
                            "id" => $profile->position->id,
                            "position_name" =>
                                $profile->position->position_name,
                        ]
                        : null,
                    "tahun_ajaran" => $profile->tahunAjaran
                        ? [
                            "id" => $profile->tahunAjaran->id,
                            "tahun" => $profile->tahunAjaran->tahun,
                            "semester" => $profile->tahunAjaran->semester,
                        ]
                        : null,
                    "role_petugas" => $profile->rolePetugas
                        ? [
                            "id" => $profile->rolePetugas->id,
                            "name" => $profile->rolePetugas->name,
                        ]
                        : null,
                    "kelas_diampu" => $profile->kelas->map(function ($k) {
                        return [
                            "id" => $k->id,
                            "nama_kelas" => $k->nama_kelas,
                        ];
                    }),
                ];
            }
        }

        return response()->json([
            "success" => true,
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "username" => $user->username,
                "rfid_no" => $user->rfid_no,
                "roles" => $user->roles->pluck("name"),
                "permissions" => $user->getAllPermissions()->pluck("name"),
            ],
            "user_type" => $userType,
            "profile" => $profile,
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth("api")->logout();

        return response()->json([
            "success" => true,
            "message" => "Successfully logged out",
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth("api")->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            "success" => true,
            "access_token" => $token,
            "token_type" => "bearer",
            "expires_in" => auth("api")->factory()->getTTL() * 60,
            "user" => auth("api")->user(),
        ]);
    }
}
