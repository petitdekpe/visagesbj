import { Controller } from '@hotwired/stimulus';

/*
 * Lite YouTube embed: shows a thumbnail + play button until clicked, so the
 * page never loads YouTube's own scripts/cookies up front — same "wait for
 * an explicit click" spirit as the campaign song's audio-consent player.
 *
 * The anthem audio and this video share the page's sound, so only one may
 * play at a time: starting the video announces it via a "media:playing"
 * document event (the audio-consent controller listens for this and pauses
 * itself), and if the anthem starts while the video is playing, this
 * controller tears the iframe down in response to that same event.
 */
export default class extends Controller {
    static values = { videoId: String };

    connect() {
        this.initialHTML = this.element.innerHTML;
        this.onMediaPlaying = this.onMediaPlaying.bind(this);
        document.addEventListener('media:playing', this.onMediaPlaying);
    }

    disconnect() {
        document.removeEventListener('media:playing', this.onMediaPlaying);
    }

    play() {
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube-nocookie.com/embed/${this.videoIdValue}?autoplay=1&rel=0`;
        iframe.title = 'Lecteur vidéo YouTube';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.frameBorder = '0';

        this.element.replaceChildren(iframe);
        document.dispatchEvent(new CustomEvent('media:playing', { detail: { source: 'video' } }));
    }

    onMediaPlaying(event) {
        if (event.detail.source !== 'video' && this.element.querySelector('iframe')) {
            this.element.innerHTML = this.initialHTML;
        }
    }
}
