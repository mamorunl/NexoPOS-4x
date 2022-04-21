<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException as MainValidationException;

class ValidationException extends MainValidationException
{
    protected $message;

    public function __construct( $message = null ) 
    {
        $this->message  =   $message ?: __('An error occured while validating the form.' );
    }

    public function render( $request )
    {
        if ( ! $request->expectsJson() ) {
            return response()->view( 'pages.errors.not-allowed', [
                'title'         =>  __( 'Unable to proceed the form is not valid' ),
                'message'       =>  $this->message
            ]);
        }

        return response()->json([ 
            'status'  =>    'failed',
            'message' =>    $this->message,
            'data'    =>    [
                'errors'    =>  $this->toHumanError()
            ]
        ], 422 );
    }

    /**
     * We'll return human understandable errors
     * @return array $errors
     */
    private function toHumanError()
    {
        $errors     =   [];
        
        if ( $this->validator ) {
            $errors     =   $this->errors();
    
            $errors     =   collect( $errors )->map( function( $messages ) {
                return collect( $messages )->map( function( $message ) {
                    switch( $message ) {
                        case 'validation.unique' :  return __( 'This value is already in use on the database.' );
                        case 'validation.required' :  return __( 'This field is required.' );
                        case 'validation.array' :  return __( 'This field does\'nt have a valid value.' );
                        case 'validation.accepted' :  return __( 'This field should be checked.' );
                        case 'validation.active_url' :  return __( 'This field must be a valid URL.' );
                        case 'validation.email' :  return __( 'This field is not a valid email.' );
                        default: return $message;
                    }
                });
            });

        }
        
        return $errors;
    }
}
