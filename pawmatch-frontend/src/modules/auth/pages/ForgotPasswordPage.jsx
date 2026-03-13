import { useState } from 'react';
import { Link } from 'react-router-dom';
import authService from '../../../services/authService';
import { PawPrint, Mail, ArrowLeft, CheckCircle, AlertCircle } from 'lucide-react';

export const ForgotPasswordPage = () => {
    const [email, setEmail] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [success, setSuccess] = useState(false);
    const [error, setError] = useState('');

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setIsSubmitting(true);

        try {
            await authService.forgotPassword(email);
            setSuccess(true);
        } catch (err) {
            console.error('Error:', err);
            if (err.response?.data?.message) {
                setError(err.response.data.message);
            } else {
                setError('Error al procesar la solicitud. Intenta de nuevo.');
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div className="max-w-md w-full bg-white p-8 rounded-2xl shadow-lg border border-gray-100">

                {/* Encabezado */}
                <div className="text-center mb-8">
                    <PawPrint className="mx-auto h-12 w-12 text-primary-600" />
                    <h2 className="mt-4 text-3xl font-extrabold text-gray-900">
                        ¿Olvidaste tu contraseña?
                    </h2>
                    <p className="mt-2 text-sm text-gray-600">
                        Ingresa tu correo y te enviaremos un enlace para restablecerla
                    </p>
                </div>

                {/* Mensaje de éxito */}
                {success ? (
                    <div className="space-y-4">
                        <div className="bg-green-50 border border-green-200 text-green-700 p-6 rounded-xl flex items-start gap-4">
                            <CheckCircle className="w-6 h-6 flex-shrink-0 mt-0.5" />
                            <div>
                                <p className="font-bold text-lg mb-1">¡Correo enviado!</p>
                                <p className="text-sm">
                                    Si existe una cuenta con ese correo, recibirás un enlace para restablecer tu contraseña.
                                </p>
                                <p className="text-sm mt-2">
                                    Revisa tu bandeja de entrada y la carpeta de spam.
                                </p>
                            </div>
                        </div>

                        <Link
                            to="/login"
                            className="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition font-medium"
                        >
                            <ArrowLeft className="w-5 h-5" />
                            Volver al inicio de sesión
                        </Link>
                    </div>
                ) : (
                    <>
                        {/* Mensaje de error */}
                        {error && (
                            <div className="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md flex items-center">
                                <AlertCircle className="h-5 w-5 text-red-500 mr-2 flex-shrink-0" />
                                <p className="text-sm text-red-700">{error}</p>
                            </div>
                        )}

                        {/* Formulario */}
                        <form className="space-y-6" onSubmit={handleSubmit}>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Correo Electrónico
                                </label>
                                <div className="relative">
                                    <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                                    <input
                                        type="email"
                                        required
                                        value={email}
                                        onChange={(e) => setEmail(e.target.value)}
                                        className="appearance-none block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                        placeholder="tu@email.com"
                                    />
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={isSubmitting}
                                className={`w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors ${isSubmitting ? 'opacity-70 cursor-not-allowed' : ''
                                    }`}
                            >
                                {isSubmitting ? 'Enviando...' : 'Enviar enlace de recuperación'}
                            </button>
                        </form>

                        {/* Link a login */}
                        <div className="mt-6 text-center">
                            <Link
                                to="/login"
                                className="text-sm font-medium text-primary-600 hover:text-primary-500 flex items-center justify-center gap-1"
                            >
                                <ArrowLeft className="w-4 h-4" />
                                Volver al inicio de sesión
                            </Link>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};