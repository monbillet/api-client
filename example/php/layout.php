<!doctype html>
<html lang="en">

<head>
    <title>monbillet.ch • API</title>

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

        <?php if($page === 'event' && $event) {?>
            <!-- Show detailed informations about a specific event -->
            <p>
                <a href="/"> <&nbsp;Liste des événements</a>&nbsp;
            </p>

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Content</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">preTitle</th>
                        <td><?= $event['preTitle'] ?></td>
                    </tr>
                    <tr>
                        <th scope="row">title</th>
                        <td><?= $event['title'] ?></td>
                    </tr>
                    <tr>
                        <th scope="row">location</th>
                        <td>
                            <div>
                                <b><?= $event['location']['name'] ?></b><br>
                                <?= $event['location']['street'] ?><br>
                                <?= $event['location']['zipcode'] ?> <?= $event['location']['city'] ?><br>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">shows</th>
                        <td>
                            <div>
                                <?php foreach ($event['shows'] as $show) {?>
                                    <?= $show['fullDateHappensHuman'] ?><br>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">aside</th>
                        <td><?= $event['aside'] ?></td>
                    </tr>
                    <tr>
                        <th scope="row">text</th>
                        <td><?= $event['text'] ?></td>
                    </tr>
                    <tr>
                        <th scope="row">bg</th>
                        <td><img src='<?= $event['bg'] ?>' alt="Affiche de l'évènement" width=500px></td>
                    </tr>
                </tbody>
            </table>

        <?php } else if($page === 'event-groups') { ?>
            <!-- Show a list of the events ordered by groups -->

            <h3 class="my-5"> 
                <a class="mr-3" href="?page=events">Événements</a>
                <u><a class="ml-3 active" href="?page=event-groups">Groupes d'événements</a></u>
            </h3>

            <?php foreach ($event_groups as $event_group) {?>
                <h4><?= $event_group['title'] ?></h4>
                <br>
                <div class="row section-body row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1">
                    <?php foreach ($event_group['events'] as $event) {?>
                        <article class="col mb-4 card-container">
                            <div class="card card-event">
                                <img class="card-img-top" alt="Affiche de l'évènement" src="<?=$event['posterMd']?>">
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

        <?php } else if($page === 'events') { ?>
            <!-- Show a list of the events -->
            <section>
                <h3 class="my-5"> 
                    <u><a class="mr-3" href="?page=events">Événements</a></u>
                    <a class="ml-3 active" href="?page=event-groups">Groupes d'événements</a></u>
                </h3>
                
                <div class="row section-body row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1">
                    <?php foreach ($events as $event) {?>
                        <article class="col mb-4 card-container">
                            <div class="card card-event">
                                <img class="card-img-top" alt="Affiche de l'évènement" src="<?=$event['posterMd']?>">
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
        <?php } else  { ?>
            404
        <?php } ?><!-- end if -->
    </div>

</body>
</html>
