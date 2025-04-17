<!doctype html>
<html lang="fr">

<head>
    <title>monbillet.ch · Exemple API</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1 user-scalable=no maximum-scale=1.0">

    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/site.webmanifest">

    <link rel="stylesheet" href="/styles/css/monbillet.css">
</head>

<body>
    <div class="container py-3">
        <?php if (isset($error))  { ?>
            <div class="alert alert-danger">
                <?= $error ?>
            </div>
        <?php }else if ($page === 'event') {?>
            <!-- Show detailed informations about a specific event -->
            <p>
                <a href="/">← Liste des événements</a>
            </p>

            <table class="table table-bordered">
                <thead>
                    <tr class="bg-secondary text-light">
                        <th scope="col">Nom</th>
                        <th scope="col">Détail</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Pré-titre</th>
                        <td><?= $event['preTitle'] ?></td>
                    </tr>
                    <tr>
                        <th>Titre</th>
                        <td><?= $event['title'] ?></td>
                    </tr>
                    <tr>
                        <th>Lieu</th>
                        <td>
                            <div>
                                <b><?= $event['location']['name'] ?></b><br>
                                <?= $event['location']['street'] ?><br>
                                <?= $event['location']['zipcode'] ?> <?= $event['location']['city'] ?><br>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Dates</th>
                        <td>
                            <div>
                                <?php foreach ($event['shows'] as $show) {?>
                                    <?= $show['fullDateHappensHuman'] ?><br>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td><?= $event['text'] ?></td>
                    </tr>
                    <tr>
                        <th>Affiche <small>Grande taille</small></th>
                        <td><img src='<?= $event['posterPreview'] ?>' alt="Image de l'évènement" width=500px></td>
                    </tr>
                </tbody>
            </table>

        <?php } else if ($page === 'event-groups') { ?>
            <!-- Show a list of the events ordered by groups -->

            <h3 class="my-5"> 
                <span class="font-weight-normal"><a class="mr-3" href="?page=events">Événements</a></span>
                <b><a class="active d-block d-sm-inline" href="?page=event-groups">Groupes d'événements</a></b>
            </h3>

            <?php foreach ($event_groups as $event_group) {?>
                <h4><?= $event_group['title'] ?></h4>
                <br>
                <div class="row section-body row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1">
                    <?php foreach ($event_group['events'] as $event) {?>
                        <article class="col mb-4 card-container">
                            <div class="card card-event">
                                <img class="card-img-top" alt="Affiche de l'évènement" src="<?=$event['posterPreview']?>">
                                <div class="card-body">
                                    <a href="?page=event&q=<?=$event['uniqueName']?>" class="stretched-link">
                                    <h3 class="card-title">
                                        <small class="pretitle">
                                            <?= $event['preTitle'] ?>
                                        </small>
                                        <span class="title">
                                            <?= $event['title'] ?>
                                        </span>
                                    </h3>
                                    </a>
                                </div>
                                <div class="card-footer">
                                    <div class="date">
                                        <?= $event['datesHuman'] ?>
                                    </div>
                                    <div class="location">
                                        <?= $event['location']['displayName'] ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php } ?><!-- end foreach -->
                </div>
            </section>
                <br>
            <?php } ?><!-- end foreach -->

        <?php } else if ($page === 'events') { ?>
            <!-- Show a list of the events -->
            <section>
                <h3 class="my-5"> 
                    <b><a class="mr-3 active" href="?page=events">Événements</a></b>
                    <span class="font-weight-normal"><a class="d-block d-sm-inline" href="?page=event-groups">Groupes d'événements</a></span>
                </h3>
                
                <div class="row section-body row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1">
                    <?php foreach ($events as $event) {?>
                        <article class="col mb-4 card-container">
                            <div class="card card-event">
                                <img class="card-img-top" alt="Affiche de l'évènement" src="<?=$event['posterPreview']?>">
                                <div class="card-body">
                                    <a href="?page=event&q=<?=$event['uniqueName']?>" class="stretched-link">
                                    <h3 class="card-title">
                                        <small class="pretitle">
                                            <?= $event['preTitle'] ?>
                                        </small>
                                        <span class="title">
                                            <?= $event['title'] ?>
                                        </span>
                                    </h3>
                                    </a>
                                </div>
                                <div class="card-footer">
                                    <div class="date">
                                        <?= $event['datesHuman'] ?>
                                    </div>
                                    <div class="location">
                                        <?= $event['location']['displayName'] ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php } ?><!-- end foreach -->
                </div>
            </section>
        <?php } ?><!-- end if -->
    </div>

</body>
</html>
