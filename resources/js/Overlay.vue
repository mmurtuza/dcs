<template>
    <div id="overlay">
      <div class="spinner-cont">
        <span class="dcs-spinner"></span>
      </div>
    </div>
</template>

<script>
export default {
    name: 'overlay'
}
</script>

<style scoped>
    #overlay{
    position: fixed;
    top: 0;
    z-index: 100;
    width: 100dvw;
    height:100%;
    display: none;
    background: rgba(0,0,0,0.6);
    }
    .spinner-cont {
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    }
    .dcs-spinner {
    width: 40px;
    height: 40px;
    border: 4px #ddd solid;
    border-top: 4px #2e93e6 solid;
    border-radius: 50%;
    animation: spin-animation 0.8s infinite linear;
    }
    @keyframes spin-animation {
    100% {
        transform: rotate(360deg);
    }
    }
    .is-hide{
    display:none;
    }
</style>
