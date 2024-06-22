<?php

namespace Config;

use CodeIgniter\Validation\CreditCardRules;
use CodeIgniter\Validation\FileRules;
use CodeIgniter\Validation\FormatRules;
use CodeIgniter\Validation\Rules;

class Validation
{
    //--------------------------------------------------------------------
    // Setup
    //--------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var string[]
     */
    public $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    //--------------------------------------------------------------------
	// Rules
	//--------------------------------------------------------------------
	public $auth = [
		'username'      => 'required',
		'password'      => 'required'
	];

	public $auth_errors = [
		'username'=> [
			'required' 	=> 'User ID wajib diisi.'
		],
		'password'=> [
			'required' 	=> 'Password wajib diisi.'
		]
	];

    public $signup = [
        'first_name' => 'required',
		'username'   => 'required',
		'nohp'       => 'required',
		'email'      => 'required',
		'password'   => 'required'
	];

	public $signup_errors = [
		'first_name'=> [
			'required' 	=> 'Nama wajib diisi.'
		],
		'username'=> [
			'required' 	=> 'Usernama wajib diisi.'
		],
		'nohp'=> [
			'required' 	=> 'No. handphone wajib diisi.'
		],
		'email'=> [
			'required' 	=> 'Email wajib diisi.'
		],
		'password'=> [
			'required' 	=> 'Password wajib diisi.'
		]
	];

}
