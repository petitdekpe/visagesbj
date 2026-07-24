import { Controller } from '@hotwired/stimulus';

/*
 * Lite YouTube embed: shows a thumbnail + play button until clicked, so the
 * page never loads YouTube's own scripts/cookies up front — same "wait for
 * an explicit click" spirit as the campaign song's audio-consent player.
 */
export default class extends Controller {
    static values = { videoId: String };

    play() {
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube-nocookie.com/embed/${this.videoIdValue}?autoplay=1&rel=0`;
        iframe.title = 'Lecteur vidéo YouTube';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.frameBorder = '0';

        this.element.replaceChildren(iframe);
    }
}
