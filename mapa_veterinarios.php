<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Veterinários Próximos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Open Sans', sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; }
        h1, h2 { font-family: 'Lato', sans-serif; }
        #root { display: flex; justify-content: center; align-items: center; width: 100%; min-height: 100vh; }
        .map-container { width: 100%; border-radius: 0.75rem; overflow: hidden; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/18.2.0/umd/react.production.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.2.0/umd/react-dom.production.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.26.0/babel.min.js"></script>
    </head>
<body>
    <div id="root"></div>

    <script type="text/babel">
        const MapComponent = () => {
            const mapRef = React.useRef(null);
            const [map, setMap] = React.useState(null);
            const [userLocation, setUserLocation] = React.useState(null);
            const [searchRadius, setSearchRadius] = React.useState(5000); 
            const [loading, setLoading] = React.useState(true);
            const [error, setError] = React.useState(null);
            const [infoMessage, setInfoMessage] = React.useState(null);
            const [veterinariosLocais, setVeterinariosLocais] = React.useState([]);

            const GOOGLE_MAPS_API_KEY = 'AIzaSyAg8eJgQFuqpD1TKi0s3a0hlX5CHUteqow'; 

            const loadGoogleMapsScript = (apiKey) => {
                if (window.google && window.google.maps) {
                    return Promise.resolve(); 
                }
                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places`; 
                    script.async = true;
                    script.defer = true;
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error('Falha ao carregar a API do Google Maps.'));
                    document.head.appendChild(script);
                });
            };

            React.useEffect(() => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            setUserLocation({
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                            });
                            setLoading(false);
                        },
                        (err) => {
                            console.error("Erro ao obter localização: ", err);
                            let errorMessage = "Não foi possível obter sua localização.";
                            if (err && err.code) {
                                if (err.code === err.PERMISSION_DENIED) {
                                    errorMessage += " Por favor, permita o acesso à localização no seu navegador.";
                                } else if (err.code === err.POSITION_UNAVAILABLE) {
                                    errorMessage += " A informação de localização não está disponível.";
                                } else if (err.code === err.TIMEOUT) {
                                    errorMessage += " A solicitação de localização expirou.";
                                }
                            } else {
                                errorMessage += " Motivo desconhecido. Verifique as permissões do navegador e a conexão HTTPS.";
                            }
                            setError(errorMessage + " Usando localização padrão (Brasília).");
                            setUserLocation({ lat: -15.7801, lng: -47.9292 }); 
                            setLoading(false);
                        },
                        { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                    );
                } else {
                    setError("Geolocalização não é suportada por este navegador. Usando localização padrão (Brasília).");
                    setUserLocation({ lat: -15.7801, lng: -47.9292 }); 
                    setLoading(false);
                }
            }, []);

            React.useEffect(() => {
                if (userLocation && GOOGLE_MAPS_API_KEY && mapRef.current) {
                    setInfoMessage("Carregando API do Google Maps..."); 
                    loadGoogleMapsScript(GOOGLE_MAPS_API_KEY)
                        .then(() => {
                            console.log("Google Maps API carregada. window.google:", window.google); 
                            if (!window.google || !window.google.maps) {
                                throw new Error('Google Maps API não carregada corretamente após script.');
                            }

                            const googleMap = new window.google.maps.Map(mapRef.current, {
                                center: userLocation,
                                zoom: 14,
                                gestureHandling: 'greedy',
                            });
                            setMap(googleMap);
                            setInfoMessage(null); 
                            new window.google.maps.Marker({
                                position: userLocation,
                                map: googleMap,
                                title: 'Sua Localização',
                                icon: {
                                    url: "http://maps.gstatic.com/mapfiles/ms/icons/blue-dot.png" 
                                }
                            });

                            
                            fetch(`buscar_veterinarios.php?latitude=${userLocation.lat}&longitude=${userLocation.lng}&raio=${searchRadius / 1000}`)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (data.success) {
                                        setVeterinariosLocais(data.veterinarios);
                                        data.veterinarios.forEach((vet) => {
                                            const marker = new window.google.maps.Marker({
                                                map: googleMap,
                                                position: { lat: parseFloat(vet.latitude), lng: parseFloat(vet.longitude) },
                                                title: vet.nome_clinica,
                                            });

                                            const infoWindow = new window.google.maps.InfoWindow({
                                                content: `
                                                    <div class="p-2">
                                                        <h3 class="font-bold text-lg mb-1">${vet.nome_clinica}</h3>
                                                        <p class="text-sm">Endereço: ${vet.endereco}</p>
                                                        <p class="text-sm">Telefone: ${vet.telefone}</p>
                                                        <p class="text-sm">Distância: ${vet.distance ? vet.distance.toFixed(2) + ' km' : 'N/A'}</p>
                                                    </div>
                                                `,
                                            });

                                            marker.addListener('click', () => {
                                                infoWindow.open(googleMap, marker);
                                            });
                                        });
                                        if (data.veterinarios.length === 0) {
                                            setInfoMessage(data.message); 
                                            setError(null); 
                                        } else {
                                            setInfoMessage(null); 
                                            setError(null); 
                                        }
                                    } else {
                                        setError(data.message || 'Erro desconhecido ao buscar veterinários do backend.');
                                        setInfoMessage(null); 
                                    }
                                })
                                .catch(err => {
                                    console.error('Erro ao buscar veterinários do backend:', err);
                                    setError('Erro ao carregar veterinários: ' + err.message);
                                    setInfoMessage(null); 
                                });
                        })
                        .catch((err) => {
                            console.error("Erro ao carregar ou inicializar Google Maps API:", err); 
                            setError('Erro ao carregar o mapa: ' + err.message);
                            setInfoMessage(null);
                        });
                }
            }, [userLocation, GOOGLE_MAPS_API_KEY, searchRadius]); 

            const handleRadiusChange = (e) => {
                setSearchRadius(parseInt(e.target.value));
            };

            if (loading) {
                return <div className="flex justify-center items-center h-screen text-xl">Carregando mapa e sua localização...</div>;
            }

            return (
                <div className="p-4 max-w-4xl mx-auto bg-white rounded-lg shadow-lg my-8">
                    <h1 className="text-3xl font-bold text-center mb-6 text-red-600">Veterinários Próximos</h1>
                    {error && <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">{error}</div>}
                    {infoMessage && <div className="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="info">{infoMessage}</div>}
                    
                    <div className="mb-4">
                        <label htmlFor="radius" className="block text-gray-700 text-sm font-bold mb-2">
                            Raio de Busca (metros):
                        </label>
                        <input
                            type="range"
                            id="radius"
                            name="radius"
                            min="1000"
                            max="20000"
                            step="1000"
                            value={searchRadius}
                            onChange={handleRadiusChange}
                            className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer range-lg"
                        />
                        <p className="text-center text-gray-600 mt-2">{searchRadius / 1000} km</p>
                    </div>

                    <div
                        ref={mapRef}
                        className="w-full rounded-lg shadow-md map-container"
                        style={{ height: '500px' }}
                        aria-label="Mapa de veterinários próximos"
                    ></div>
                    <p className="text-sm text-gray-500 mt-4 text-center">
                        Os resultados são fornecidos pelo seu banco de dados local.
                    </p>
                </div>
            );
        };

        ReactDOM.createRoot(document.getElementById('root')).render(<MapComponent />);
    </script>
</body>
</html>