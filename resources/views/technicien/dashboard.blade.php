@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Mon Espace Technicien</h1>

    <!-- Carte des interventions -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Carte des interventions</h5>
        </div>
        <div class="card-body">
            <div id="map" style="height: 400px;"></div>
        </div>
    </div>

    <!-- Réclamations en cours -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Réclamations en cours</h5>
        </div>
        <div class="card-body">
            @if($reclamations_en_cours->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Bâtiment</th>
                                <th>Local/Chambre</th>
                                <th>Demandeur</th>
                                <th>Sujet</th>
                                <th>Priorité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reclamations_en_cours as $reclamation)
                            <tr>
                                <td>{{ $reclamation->created_at->format('d/m/Y') }}</td>
                                <td>{{ $reclamation->local->batiment->nom }}</td>
                                <td>{{ $reclamation->local->nom }}</td>
                                <td>
                                    @if($reclamation->commercant)
                                        {{ $reclamation->commercant->nom }} {{ $reclamation->commercant->prenom }}
                                        <br><small class="text-muted">Commerçant</small>
                                    @else
                                        {{ $reclamation->etudiant->nom }} {{ $reclamation->etudiant->prenom }}
                                        <br><small class="text-muted">Étudiant</small>
                                    @endif
                                </td>
                                <td>{{ $reclamation->sujet }}</td>
                                <td>
                                    <span class="badge bg-{{ $reclamation->priorite_color }}">
                                        {{ ucfirst($reclamation->priorite) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('technicien.reclamations.show', $reclamation->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#terminerModal{{ $reclamation->id }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>

                                    <!-- Modal Terminer -->
                                    <div class="modal fade" id="terminerModal{{ $reclamation->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Terminer la réclamation</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('technicien.reclamations.terminer', $reclamation->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Commentaire de résolution</label>
                                                            <textarea name="commentaire" 
                                                                      class="form-control" 
                                                                      rows="4" 
                                                                      required
                                                                      placeholder="Décrivez les travaux effectués et la résolution du problème"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-success">Marquer comme terminé</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center mb-0">Aucune réclamation en cours</p>
            @endif
        </div>
    </div>

    <!-- Réclamations terminées -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Réclamations terminées</h5>
        </div>
        <div class="card-body">
            @if($reclamations_terminees->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Bâtiment</th>
                                <th>Local/Chambre</th>
                                <th>Demandeur</th>
                                <th>Sujet</th>
                                <th>Date de résolution</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reclamations_terminees as $reclamation)
                            <tr>
                                <td>{{ $reclamation->created_at->format('d/m/Y') }}</td>
                                <td>{{ $reclamation->local->batiment->nom }}</td>
                                <td>{{ $reclamation->local->nom }}</td>
                                <td>
                                    @if($reclamation->commercant)
                                        {{ $reclamation->commercant->nom }} {{ $reclamation->commercant->prenom }}
                                        <br><small class="text-muted">Commerçant</small>
                                    @else
                                        {{ $reclamation->etudiant->nom }} {{ $reclamation->etudiant->prenom }}
                                        <br><small class="text-muted">Étudiant</small>
                                    @endif
                                </td>
                                <td>{{ $reclamation->sujet }}</td>
                                <td>{{ $reclamation->date_resolution->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('technicien.reclamations.show', $reclamation->id) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center mb-0">Aucune réclamation terminée</p>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap" async defer></script>
<script>
    function initMap() {
        // Position par défaut (centre de la ville)
        const defaultPosition = {
            lat: 6.3702928, 
            lng: 2.3912362
        };

        // Création de la carte
        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 13,
            center: defaultPosition,
            mapTypeId: 'roadmap'
        });

        // Récupération des réclamations en cours
        const reclamations = @json($reclamations_en_cours);
        
        reclamations.forEach(reclamation => {
            if (reclamation.local.batiment.localisation_lat && reclamation.local.batiment.localisation_lng) {
                const marker = new google.maps.Marker({
                    position: {
                        lat: parseFloat(reclamation.local.batiment.localisation_lat),
                        lng: parseFloat(reclamation.local.batiment.localisation_lng)
                    },
                    map: map,
                    title: reclamation.local.batiment.nom,
                    icon: {
                        url: getPriorityIcon(reclamation.priorite),
                        scaledSize: new google.maps.Size(30, 30)
                    }
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div class="info-window">
                            <h5>${reclamation.local.batiment.nom}</h5>
                            <p><strong>Local:</strong> ${reclamation.local.nom}</p>
                            <p><strong>Sujet:</strong> ${reclamation.sujet}</p>
                            <p><strong>Priorité:</strong> 
                                <span class="badge bg-${getPriorityColor(reclamation.priorite)}">
                                    ${reclamation.priorite}
                                </span>
                            </p>
                            <a href="/technicien/reclamations/${reclamation.id}" class="btn btn-sm btn-info">
                                Voir les détails
                            </a>
                        </div>
                    `
                });

                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });
            }
        });
    }

    function getPriorityIcon(priority) {
        switch(priority) {
            case 'urgent':
                return '/images/markers/urgent.png';
            case 'normal':
                return '/images/markers/normal.png';
            default:
                return '/images/markers/low.png';
        }
    }

    function getPriorityColor(priority) {
        switch(priority) {
            case 'urgent':
                return 'danger';
            case 'normal':
                return 'warning';
            default:
                return 'info';
        }
    }
</script>
@endpush

@push('styles')
<style>
    .info-window {
        padding: 10px;
        max-width: 250px;
    }
    .info-window h5 {
        margin-bottom: 10px;
    }
    .info-window .badge {
        display: inline-block;
        padding: 0.25em 0.6em;
        font-size: 12px;
    }
</style>
@endpush
@endsection 