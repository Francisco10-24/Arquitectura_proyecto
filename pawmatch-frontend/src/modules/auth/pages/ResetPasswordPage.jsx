import { useState, useEffect } from 'react';
import { useSearchParams, useNavigate, Link } from 'react-router-dom';
import authService from '../../../services/authService';
import { PawPrint, Lock, CheckCircle, AlertCircle } from 'lucide-react';

export const ResetPasswordPage = () => {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();

    const token = searchParams.get('token');
    const email = searchParams.get('email');

    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [success, setSuccess] = useState(false);
    const [error, setError] = useState('');
    const [errors, setErrors] = useState({});

    useEffect(() => {
        if (!token || !email) {
            setError('Enlace inválido o incompleto');
        }
    }, [token, email]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setErrors({});
        setIsSubmitting(true);

        try {
            await authService.resetPassword({
                token,
                email,
                password,
                password_confirmation: passwordConfirmation,
            });

            setSuccess(true);

            // Redirigir al login después de 3 segundos
            setTimeout(() => {
                navigate('/login');
            }, 3000);

        } catch (err) {
            console.error('Error:', err);

            if (err.response?.status === 422) {
                setErrors(err.response.data.errors || {});
                if (err.response.data.errors?.token) {
                    setError(err.response.data.errors.token[0]);
                }
            } else if (err.response?.data?.message) {
                setError(err.response.data.message);
            } else {
                setError('Error al restablecer la contraseña. Intenta de nuevo.');
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    if (!token || !email) {
        return (
            <div className="min-h-[70vh] flex items-center justify-center py-12 px-4">
                <div className="max-w-md w-full bg-white p-8 rounded-2xl shadow-lg border border-gray-100 text-center">
                    <AlertCircle className="mx-auto h-12 w-12 text-red-500 mb-4" />
                    <h2 className="text-2xl font-bold text-gray-900 mb-2">
                        Enlace inválido
                    </h2>
                    <p className="text-gray-600 mb-6">
                        El enlace de recuperación es inválido o ha expirado.
                    </p>
                    <Link
                        to="/forgot-password"
                        className="inline-block px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium"
                    >
                        Solicitar nuevo enlace
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div className="max-w-md w-full bg-white p-8 rounded-2xl shadow-lg border border-gray-100">

                {/* Encabezado */}
                <div className="text-center mb-8">
                    <PawPrint className="mx-auto h-12 w-12 text-primary-600" />
                    <h2 className="mt-4 text-3xl font-extrabold text-gray-900">
                        Restablecer contraseña
                    </h2>
                    <p className="mt-2 text-sm text-gray-600">
                        Ingresa tu nueva contraseña para {email}
                    </p>
                </div>

                {/* Mensaje de éxito */}
                {success ? (
                    <div className="space-y-4">
                        <div className="bg-green-50 border border-green-200 text-green-700 p-6 rounded-xl flex items-start gap-4">
                            <CheckCircle className="w-6 h-6 flex-shrink-0 mt-0.5" />
                            <div>
                                <p className="font-bold text-lg mb-1">
                                    ¡Contraseña restablecida!
                                </p>
                                <p className="text-sm">
                                    Tu contraseña ha sido actualizada exitosamente.
                                </p>
                                <p className="text-sm mt-2">
                                    Redirigiendo al inicio de sesión...
                                </p>
                            </div>
                        </div>
                    </div>
                ) : (
                    <>
                        {/* Mensaje de error general */}
                        {error && (
                            <div className="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md flex items-center">
                                <AlertCircle className="h-5 w-5 text-red-500 mr-2 flex-shrink-0" />
                                <p className="text-sm text-red-700">{error}</p>
                            </div>
                        )}

                        {/* Formulario */}
                        <form className="space-y-6" onSubmit={handleSubmit}>

                            {/* Nueva contraseña */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Nueva contraseña
                                </label>
                                <div className="relative">
                                    <Lock className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                                    <input
                                        type="password"
                                        required
                                        minLength={8}
                                        value={password}
                                        onChange={(e) => setPassword(e.target.value)}
                                        className="appearance-none block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                        placeholder="Mínimo 8 caracteres"
                                    />
                                </div>
                                {errors.password && (
                                    <p className="mt-1 text-sm text-red-600">{errors.password[0]}</p>
                                )}
                            </div>

                            {/* Confirmar contraseña */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Confirmar contraseña
                                </label>
                                <div className="relative">
                                    <Lock className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                                    <input
                                        type="password"
                                        required
                                        value={passwordConfirmation}
                                        onChange={(e) => setPasswordConfirmation(e.target.value)}
                                        className="appearance-none block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                        placeholder="Repite la contraseña"
                                    />
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={isSubmitting}
                                className={`w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors ${isSubmitting ? 'opacity-70 cursor-not-allowed' : ''
                                    }`}
                            >
                                {isSubmitting ? 'Restableciendo...' : 'Restablecer contraseña'}
                            </button>
                        </form>

                        {/* Link a login */}
                        <div className="mt-6 text-center">
                            <Link
                                to="/login"
                                className="text-sm font-medium text-primary-600 hover:text-primary-500"
                            >
                                Volver al inicio de sesión
                            </Link>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};