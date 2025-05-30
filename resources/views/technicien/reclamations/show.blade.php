@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('technicien.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
        </a>
    </div>

    <div class="row">
        <!-- Détails de la réclamation -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Détails de la réclamation</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Informations générales</h6>
                            <p><strong>Date de création :</strong> {{ $reclamation->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Statut :</strong> 
                                <span class="badge bg-{{ $reclamation->statut === 'termine' ? 'success' : 'warning' }}">
                                    {{ ucfirst($reclamation->statut) }}
                                </span>
                            </p>
                            <p><strong>Priorité :</strong> 
                                <span class="badge bg-{{ $reclamation->priorite_color }}">
                                    {{ ucfirst($reclamation->priorite) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Localisation</h6>
                            <p><strong>Bâtiment :</strong> {{ $reclamation->local->batiment->nom }}</p>
                            <p><strong>Local/Chambre :</strong> {{ $reclamation->local->nom }}</p>
                            <p><strong>Adresse :</strong> {{ $reclamation->local->batiment->adresse }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Demandeur</h6>
                            @if($reclamation->commercant)
                                <p><strong>Nom :</strong> {{ $reclamation->commercant->nom }} {{ $reclamation->commercant->prenom }}</p>
                                <p><strong>Type :</strong> Commerçant</p>
                                <p><strong>Téléphone :</strong> {{ $reclamation->commercant->telephone }}</p>
                            @else
                                <p><strong>Nom :</strong> {{ $reclamation->etudiant->nom }} {{ $reclamation->etudiant->prenom }}</p>
                                <p><strong>Type :</strong> Étudiant</p>
                                <p><strong>Téléphone :</strong> {{ $reclamation->etudiant->telephone }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6>Description du problème</h6>
                        <div class="p-3 bg-light rounded">
                            {{ $reclamation->description }}
                        </div>
                    </div>

                    @if($reclamation->photos->count() > 0)
                        <div class="mb-3">
                            <h6>Photos</h6>
                            <div class="row">
                                @foreach($reclamation->photos as $photo)
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ Storage::url($photo->chemin) }}" target="_blank">
                                            <img src="{{ Storage::url($photo->chemin) }}" 
                                                 class="img-fluid rounded" 
                                                 alt="Photo de la réclamation">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($reclamation->statut === 'en_cours')
                        <div class="mt-4">
                            <button type="button" 
                                    class="btn btn-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#terminerModal">
                                <i class="fas fa-check me-2"></i>Marquer comme terminé
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Messages et historique -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Messages et historique</h5>
                </div>
                <div class="card-body">
                    <div class="messages-list">
                        @foreach($reclamation->messages->sortByDesc('created_at') as $message)
                            <div class="message-item mb-3 p-3 rounded {{ $message->expediteur_id === Auth::id() ? 'bg-light' : 'border' }}">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted">
                                        {{ $message->expediteur->nom }} {{ $message->expediteur->prenom }}
                                    </small>
                                    <small class="text-muted">
                                        {{ $message->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                                <p class="mb-0">{{ $message->contenu }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Terminer -->
@if($reclamation->statut === 'en_cours')
<div class="modal fade" id="terminerModal" tabindex="-1">
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
@endif
@endsection 